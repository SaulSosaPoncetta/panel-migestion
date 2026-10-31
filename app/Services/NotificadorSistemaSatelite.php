<?php

namespace App\Services;

use App\Models\ClienteSistema;
use App\Models\Suscripcion;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Le avisa al sistema satélite que cambió el estado / abono de un cliente, para que
 * corte o reactive el acceso al instante. Si el aviso falla no pasa nada grave: el sistema
 * igual consulta GET /api/estado-cliente cuando el usuario inicia sesión.
 *
 * Hay dos avisos, ambos firmados con HMAC-SHA256 (header X-MiGestion-Signature) usando el
 * webhook_secret del sistema:
 *   1) webhook_url del sistema           → { referencia_externa, email_cliente, estado }   (formato original)
 *   2) {url_base}/api/panel/abono        → abono completo con vencimiento y cupo (formato nuevo)
 * La firma es el HMAC del cuerpo JSON exacto que se envía.
 */
class NotificadorSistemaSatelite
{
    /** Avisa por los dos canales. Es el que usan los pagos y los cambios de estado. */
    public function avisarSuscripcion(Suscripcion $suscripcion): void
    {
        $clienteSistema = $suscripcion->clienteSistema()->with(['cliente', 'sistema'])->first();

        if (! $clienteSistema) {
            return;
        }

        $this->avisarCambioEstado($clienteSistema, $suscripcion->estado);
        $this->avisarAbono($suscripcion, $clienteSistema);
    }

    /** Aviso original: solo el estado. */
    public function avisarCambioEstado(ClienteSistema $clienteSistema, string $estado): void
    {
        $sistema = $clienteSistema->sistema;

        if (! $sistema->webhook_url) {
            return;
        }

        $payload = [
            'referencia_externa' => $clienteSistema->referencia_externa,
            'email_cliente' => $clienteSistema->cliente->email,
            'estado' => $estado, // activa | vencida | suspendida | cancelada
        ];

        $firma = hash_hmac('sha256', json_encode($payload), $sistema->webhook_secret);

        try {
            Http::withHeaders(['X-MiGestion-Signature' => $firma])
                ->timeout(5)
                ->post($sistema->webhook_url, $payload)
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar al sistema satélite', [
                'sistema' => $sistema->slug,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Aviso nuevo: abono completo (estado + plan + cupo + vencimiento). */
    public function avisarAbono(Suscripcion $suscripcion, ?ClienteSistema $clienteSistema = null): void
    {
        $clienteSistema ??= $suscripcion->clienteSistema()->with(['cliente', 'sistema'])->first();
        $sistema = $clienteSistema?->sistema;

        if (! $sistema || ! $sistema->activo || ! $clienteSistema->referencia_externa || ! $sistema->url_base) {
            return;
        }

        $url = rtrim($sistema->url_base, '/').'/api/panel/abono';
        $body = json_encode($this->payloadAbono($suscripcion, $clienteSistema), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            Http::withHeaders([
                'X-MiGestion-Signature' => hash_hmac('sha256', $body, $sistema->webhook_secret),
                'X-MiGestion-Event' => 'abono.actualizado',
            ])
                ->withBody($body, 'application/json')
                ->timeout(8)
                ->post($url)
                ->throw();
        } catch (RequestException $e) {
            // 404/405: ese sistema todavía no implementó el receptor, no es un error real.
            $nivel = in_array($e->response->status(), [404, 405], true) ? 'info' : 'warning';
            Log::$nivel('Aviso de abono no recibido por el sistema satélite', [
                'sistema' => $sistema->slug,
                'http' => $e->response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el aviso de abono', [
                'sistema' => $sistema->slug,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function payloadAbono(Suscripcion $suscripcion, ClienteSistema $clienteSistema): array
    {
        $plan = $suscripcion->planCatalogo;

        return [
            'evento' => 'abono.actualizado',
            'enviado_en' => now()->toIso8601String(),
            'id_suscripcion' => $suscripcion->id_suscripcion,
            'referencia_externa' => $clienteSistema->referencia_externa,
            'email_cliente' => $clienteSistema->cliente->email,
            'estado' => $suscripcion->estado,                     // activa | vencida | suspendida | cancelada | pendiente
            'plan' => $plan?->codigo,
            'plan_nombre' => $suscripcion->plan,
            'tipo' => $suscripcion->tipo,                         // recurrente | unico
            'cantidad' => (int) $suscripcion->cantidad,           // cupo contratado (ej: docentes)
            'fecha_vencimiento' => $suscripcion->fecha_vencimiento?->toDateString(), // null = sin vencimiento
            'monto' => (float) $suscripcion->monto,
            'moneda' => $suscripcion->moneda,
        ];
    }
}
