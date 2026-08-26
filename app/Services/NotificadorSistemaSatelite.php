<?php

namespace App\Services;

use App\Models\ClienteSistema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Empuja el nuevo estado de pago al sistema satélite (webhook saliente),
 * para que corte/reactive el acceso al instante. Si falla, no pasa nada grave:
 * el sistema satélite igual va a verificar el estado por su cuenta contra
 * GET /api/estado-cliente (ver Api\EstadoClienteController) la próxima vez
 * que el usuario intente loguearse.
 */
class NotificadorSistemaSatelite
{
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
}
