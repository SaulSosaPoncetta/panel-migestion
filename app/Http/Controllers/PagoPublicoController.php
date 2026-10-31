<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteSistema;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\SistemaSaas;
use App\Models\Suscripcion;
use App\Services\EnlacePago;
use App\Services\MercadoPagoService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Página pública de pago del Panel. Se entra únicamente con un enlace firmado
 * generado por un sistema satélite (ver App\Services\EnlacePago).
 */
class PagoPublicoController extends Controller
{
    public function mostrar(Request $request)
    {
        [$sistema, $clienteSistema, $q] = $this->resolver($request);

        $planes = $sistema->planes()->where('activo', true)->orderBy('precio')->get();

        if (! empty($q['plan'])) {
            $planes = $planes->where('codigo', $q['plan'])->values();
        }

        if ($planes->isEmpty()) {
            return $this->invalido('Este sistema todavía no tiene planes disponibles para contratar. Escribinos y lo resolvemos.');
        }

        $suscripcion = $clienteSistema->suscripciones()
            ->where('estado', '!=', 'cancelada')
            ->latest('id_suscripcion')
            ->first();

        return view('pagar.index', [
            'sistema' => $sistema,
            'cliente' => $clienteSistema->cliente,
            'planes' => $planes,
            'suscripcion' => $suscripcion,
            'cantidadInicial' => (int) ($q['cantidad'] ?? 0),
            'queryFirmada' => http_build_query($q, '', '&', PHP_QUERY_RFC3986),
            'transferenciaActiva' => (bool) config('pagos.transferencia.activa'),
        ]);
    }

    public function iniciar(Request $request, MercadoPagoService $mercadoPago)
    {
        [$sistema, $clienteSistema, $q] = $this->resolver($request);

        $request->validate([
            'id_plan' => 'required|integer',
            'cantidad' => 'nullable|integer|min:1',
            'medio' => 'nullable|in:mercadopago,transferencia',
        ]);

        $medio = $request->input('medio') === 'transferencia' && config('pagos.transferencia.activa')
            ? 'transferencia'
            : 'mercadopago';

        $plan = Plan::where('id_plan', $request->id_plan)
            ->where('id_sistema', $sistema->id_sistema)
            ->where('activo', true)
            ->first();

        if (! $plan || (! empty($q['plan']) && $plan->codigo !== $q['plan'])) {
            return back()->with('error', 'El plan elegido no está disponible.');
        }

        $cantidad = 1;
        if ($plan->cobro_por_unidad) {
            $cantidad = (int) ($request->input('cantidad') ?: ($q['cantidad'] ?? $plan->unidad_minima));
            $unidad = $plan->unidad_nombre ?: 'unidades';

            if ($cantidad < $plan->unidad_minima || ($plan->unidad_maxima && $cantidad > $plan->unidad_maxima)) {
                $rango = $plan->unidad_maxima ? "entre {$plan->unidad_minima} y {$plan->unidad_maxima}" : "de al menos {$plan->unidad_minima}";

                return back()->withInput()->with('error', "La cantidad de {$unidad} debe ser {$rango}.");
            }
        }

        $datos = [
            'id_plan' => $plan->id_plan,
            'plan' => $plan->nombre,
            'tipo' => $plan->tipo,
            'cantidad' => $cantidad,
            'monto' => $plan->calcularMonto($cantidad),
            'moneda' => $plan->moneda,
        ];

        // Una sola suscripción vigente por cliente y sistema: si ya tiene una, se la actualiza
        // (así el sistema satélite siempre consulta la última y no queda "pendiente" mientras renueva).
        $suscripcion = $clienteSistema->suscripciones()
            ->where('estado', '!=', 'cancelada')
            ->latest('id_suscripcion')
            ->first();

        $cambio = false;
        if ($suscripcion) {
            $cambio = (float) $suscripcion->monto !== (float) $datos['monto']
                || (int) $suscripcion->id_plan !== (int) $datos['id_plan']
                || $suscripcion->tipo !== $datos['tipo'];
        }

        try {
            if ($suscripcion && $suscripcion->tipo === 'recurrente' && $suscripcion->mp_preapproval_id) {
                $actual = $mercadoPago->obtenerPreapproval($suscripcion->mp_preapproval_id);
                if (($actual['status'] ?? null) === 'authorized') {
                    $mensaje = $cambio
                        ? 'Tenés un débito automático activo. Para cambiar de plan o de cantidad escribinos y lo ajustamos.'
                        : 'Tu débito automático ya está activo: no hace falta que pagues manualmente.';

                    return back()->with('error', $mensaje);
                }
            }

            if ($suscripcion) {
                if ($cambio) {
                    $datos['mp_preapproval_id'] = null;
                }
                $suscripcion->update($datos);
            } else {
                $suscripcion = Suscripcion::create($datos + [
                    'id_cliente_sistema' => $clienteSistema->id_cliente_sistema,
                    'estado' => 'pendiente',
                    'fecha_inicio' => now(),
                ]);
            }

            if ($medio === 'transferencia') {
                $pago = $this->pagoTransferencia($suscripcion);

                return redirect()->to(URL::temporarySignedRoute(
                    'pagar.transferencia', now()->addDays(7), ['pago' => $pago->id_pago]
                ));
            }

            $link = $this->generarLink($suscripcion->fresh('planCatalogo'), $mercadoPago);
        } catch (\Throwable $e) {
            Log::error('No se pudo generar el pago', [
                'sistema' => $sistema->slug,
                'ref' => $clienteSistema->referencia_externa,
                'medio' => $medio,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $medio === 'transferencia'
                ? 'No pudimos preparar la transferencia. Probá de nuevo en unos minutos.'
                : 'No pudimos conectar con Mercado Pago. Probá de nuevo en unos minutos.');
        }

        return redirect()->away($link);
    }

    /** A donde vuelve Mercado Pago después del pago (éxito, error o pendiente). */
    public function resultado(Request $request, string $estado)
    {
        $suscripcion = null;

        if ($request->filled('external_reference')) {
            $suscripcion = Suscripcion::find($request->query('external_reference'));
        } elseif ($request->filled('preapproval_id')) {
            $suscripcion = Suscripcion::where('mp_preapproval_id', $request->query('preapproval_id'))->first();
        }

        $sistema = $suscripcion?->clienteSistema?->sistema;

        return view('pagar.resultado', [
            'estado' => in_array($estado, ['exito', 'error', 'pendiente'], true) ? $estado : 'pendiente',
            'sistema' => $sistema,
        ]);
    }

    /** Reutiliza el pago por transferencia pendiente del mismo monto o crea uno nuevo. */
    protected function pagoTransferencia(Suscripcion $suscripcion): Pago
    {
        $existente = $suscripcion->pagos()
            ->where('medio_pago', 'transferencia')
            ->where('estado', 'pendiente')
            ->where('monto', $suscripcion->monto)
            ->latest('id_pago')
            ->first();

        return $existente ?? Pago::create([
            'id_suscripcion' => $suscripcion->id_suscripcion,
            'medio_pago' => 'transferencia',
            'monto' => $suscripcion->monto,
            'moneda' => $suscripcion->moneda,
            'estado' => 'pendiente',
        ]);
    }

    protected function generarLink(Suscripcion $suscripcion, MercadoPagoService $mercadoPago): string
    {
        if ($suscripcion->tipo === 'recurrente') {
            if ($suscripcion->mp_preapproval_id) {
                $datos = $mercadoPago->obtenerPreapproval($suscripcion->mp_preapproval_id);
                if (($datos['status'] ?? null) === 'pending' && ! empty($datos['init_point'])) {
                    return $datos['init_point'];
                }
            }

            $resultado = $mercadoPago->crearPreapproval($suscripcion);
            $suscripcion->update(['mp_preapproval_id' => $resultado['id']]);

            return $resultado['init_point'];
        }

        return $mercadoPago->crearPreferencia($suscripcion)['init_point'];
    }

    /**
     * Valida el enlace firmado y devuelve [sistema, clienteSistema, parámetros firmados].
     * Si algo no cierra corta con la pantalla de enlace inválido.
     */
    protected function resolver(Request $request): array
    {
        $q = EnlacePago::filtrar($request->query());

        $sistema = SistemaSaas::where('slug', $q['sistema'] ?? '')->where('activo', true)->first();

        if (! $sistema || empty($q['ref']) || ! EnlacePago::verificar($q, $sistema->webhook_secret)) {
            throw new HttpResponseException($this->invalido('El enlace de pago no es válido o venció. Volvé al sistema y tocá "Pagar" de nuevo.'));
        }

        $clienteSistema = ClienteSistema::where('id_sistema', $sistema->id_sistema)
            ->where('referencia_externa', $q['ref'])
            ->with('cliente')
            ->first();

        if (! $clienteSistema) {
            if (empty($q['email']) || empty($q['nombre'])) {
                throw new HttpResponseException($this->invalido('No encontramos tu cuenta. Volvé al sistema y tocá "Pagar" de nuevo.'));
            }

            $clienteSistema = $this->altaCliente($sistema, $q);
        }

        return [$sistema, $clienteSistema, $q];
    }

    /** El sistema todavía no había registrado al cliente: lo damos de alta con los datos firmados del enlace. */
    protected function altaCliente(SistemaSaas $sistema, array $q): ClienteSistema
    {
        $cuit = Cliente::normalizarCuit($q['cuit'] ?? null);

        $cliente = ($cuit ? Cliente::where('identificacion_fiscal', $cuit)->first() : null)
            ?? Cliente::where('email', $q['email'])->first()
            ?? Cliente::create([
                'nombre' => $q['nombre'],
                'email' => $q['email'],
                'identificacion_fiscal' => $cuit,
            ]);

        $clienteSistema = ClienteSistema::firstOrNew([
            'id_cliente' => $cliente->id_cliente,
            'id_sistema' => $sistema->id_sistema,
        ]);
        $clienteSistema->fill([
            'referencia_externa' => $q['ref'],
            'email_alta' => $q['email'],
            'identificacion_fiscal_alta' => $cuit,
        ])->save();

        return $clienteSistema->load('cliente');
    }

    protected function invalido(string $mensaje)
    {
        return response()->view('pagar.invalido', ['mensaje' => $mensaje], 403);
    }
}
