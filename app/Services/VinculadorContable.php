<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ClienteSistema;
use App\Models\SistemaSaas;
use App\Models\Vinculacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Vincula automáticamente GestiónComercial con Contabilidad cuando un mismo cliente
 * (misma empresa / CUIT) tiene cuenta en los dos sistemas:
 *
 *   1. Le pide a Contabilidad un token de integración para esa empresa.
 *   2. Se lo entrega a GestiónComercial junto con la URL, y activa el envío automático.
 *
 * Todas las llamadas van firmadas (HMAC-SHA256 del cuerpo con el webhook_secret de cada sistema)
 * y llevan la hora ("ts") para que no se puedan reenviar más tarde.
 *
 * Seguridad: solo se vincula sola si el alta en ambos sistemas tiene el MISMO CUIT y el MISMO email.
 * Si no, queda "por confirmar" y la confirma el superadmin desde el panel
 * (así nadie recibe la facturación de otra empresa registrándose con su CUIT).
 */
class VinculadorContable
{
    /**
     * Revisa un cliente y vincula si corresponde. Devuelve la vinculación (o null si no tiene los dos sistemas).
     *
     * @param bool $confirmar true = lo pidió el superadmin (vincula aunque no coincidan los datos del alta)
     */
    public function evaluar(Cliente $cliente, bool $confirmar = false): ?Vinculacion
    {
        [$comercial, $contable] = $this->cuentas($cliente);

        if (! $comercial || ! $contable || ! $comercial->referencia_externa || ! $contable->referencia_externa) {
            return null;
        }

        $vinculacion = Vinculacion::firstOrNew([
            'id_cs_comercial' => $comercial->id_cliente_sistema,
            'id_cs_contable'  => $contable->id_cliente_sistema,
        ]);
        $vinculacion->id_cliente = $cliente->id_cliente;

        // Ya vinculada, o desvinculada a mano: no se toca salvo que la confirme el superadmin
        if (! $confirmar && $vinculacion->exists && in_array($vinculacion->estado, ['vinculada', 'desvinculada'], true)) {
            return $vinculacion;
        }

        if (! $confirmar && ! $this->mismoTitular($comercial, $contable)) {
            $vinculacion->estado = 'por_confirmar';
            $vinculacion->mensaje = 'El CUIT o el email con que se dio de alta en cada sistema no coinciden. Verificá que sea la misma empresa y confirmá.';
            $vinculacion->save();

            return $vinculacion;
        }

        return $this->vincular($vinculacion);
    }

    /** Genera el token en Contabilidad y se lo entrega a GestiónComercial. */
    public function vincular(Vinculacion $vinculacion): Vinculacion
    {
        $comercial = $vinculacion->comercial()->with('sistema')->first() ?? $vinculacion->comercial;
        $contable = $vinculacion->contable()->with('sistema')->first() ?? $vinculacion->contable;

        try {
            $respuesta = $this->enviar($contable->sistema, config('vinculacion.ruta_contable'), [
                'accion'             => 'vincular',
                'referencia_externa' => (string) $contable->referencia_externa,
                'origen'             => $comercial->sistema->nombre,
            ]);

            if (empty($respuesta['token']) || empty($respuesta['url'])) {
                throw new RuntimeException($contable->sistema->nombre . ': no devolvió el token de integración.');
            }

            $this->enviar($comercial->sistema, config('vinculacion.ruta_comercial'), [
                'accion'             => 'vincular',
                'referencia_externa' => (string) $comercial->referencia_externa,
                'contabilidad_url'   => $respuesta['url'],
                'contabilidad_token' => $respuesta['token'],
            ]);

            $vinculacion->fill(['estado' => 'vinculada', 'mensaje' => null, 'vinculada_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::warning('Vinculación contable fallida', ['id_cliente' => $vinculacion->id_cliente, 'error' => $e->getMessage()]);
            $vinculacion->fill(['estado' => 'error', 'mensaje' => Str::limit($e->getMessage(), 250)])->save();
        }

        return $vinculacion;
    }

    /** Corta la integración en los dos sistemas (los errores de red se registran pero no frenan). */
    public function desvincular(Vinculacion $vinculacion): void
    {
        $avisos = [];

        foreach ([[$vinculacion->contable, 'ruta_contable'], [$vinculacion->comercial, 'ruta_comercial']] as [$cuenta, $ruta]) {
            if (! $cuenta?->sistema || ! $cuenta->referencia_externa) {
                continue;
            }
            try {
                $this->enviar($cuenta->sistema, config("vinculacion.{$ruta}"), [
                    'accion'             => 'desvincular',
                    'referencia_externa' => (string) $cuenta->referencia_externa,
                ]);
            } catch (\Throwable $e) {
                $avisos[] = $e->getMessage();
            }
        }

        if ($vinculacion->exists) {
            $vinculacion->fill([
                'estado'  => 'desvinculada',
                'mensaje' => $avisos ? Str::limit('No se pudo avisar a: ' . implode(' | ', $avisos), 250) : null,
            ])->save();
        }
    }

    /** [cuenta en GestiónComercial, cuenta en Contabilidad] */
    private function cuentas(Cliente $cliente): array
    {
        $cuentas = $cliente->clienteSistemas()->with('sistema')->get();

        return [
            $cuentas->first(fn ($c) => $c->sistema?->slug === config('vinculacion.slug_comercial') && $c->sistema->activo),
            $cuentas->first(fn ($c) => $c->sistema?->slug === config('vinculacion.slug_contable') && $c->sistema->activo),
        ];
    }

    private function mismoTitular(ClienteSistema $a, ClienteSistema $b): bool
    {
        $cuitA = Cliente::normalizarCuit($a->identificacion_fiscal_alta);
        $cuitB = Cliente::normalizarCuit($b->identificacion_fiscal_alta);

        return $cuitA && $cuitA === $cuitB
            && $a->email_alta && strcasecmp(trim($a->email_alta), trim($b->email_alta ?? '')) === 0;
    }

    /** POST firmado a un sistema satélite. */
    private function enviar(SistemaSaas $sistema, string $ruta, array $datos): array
    {
        $cuerpo = json_encode($datos + ['ts' => time()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $respuesta = Http::withHeaders([
                'X-MiGestion-Signature' => hash_hmac('sha256', $cuerpo, $sistema->webhook_secret),
                'Accept'                => 'application/json',
            ])
            ->withBody($cuerpo, 'application/json')
            ->timeout(10)
            ->post(rtrim($sistema->url_base, '/') . $ruta);

        if ($respuesta->failed()) {
            $detalle = $respuesta->json('error') ?? $respuesta->json('message') ?? ('HTTP ' . $respuesta->status());
            throw new RuntimeException("{$sistema->nombre}: {$detalle}");
        }

        return $respuesta->json() ?? [];
    }
}
