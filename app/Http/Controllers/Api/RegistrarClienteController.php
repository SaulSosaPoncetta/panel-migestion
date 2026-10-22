<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ClienteSistema;
use App\Models\Suscripcion;
use App\Services\VinculadorContable;
use Illuminate\Http\Request;

class RegistrarClienteController extends Controller
{
    /**
     * POST /api/registrar-cliente
     *
     * Lo llama un sistema satélite (autenticado con su api_key) apenas un
     * usuario nuevo se da de alta, para que el hub lo empiece a controlar
     * automáticamente sin carga manual.
     *
     * Body: { referencia_externa, nombre, email, identificacion_fiscal?, telefono?, plan?, monto?, tipo? }
     *
     * El cliente del hub es la EMPRESA: se busca primero por CUIT y después por email.
     * Si la misma empresa ya tenía cuenta en otro sistema, se intenta la vinculación
     * automática (p. ej. GestiónComercial → Contabilidad).
     */
    public function __invoke(Request $request)
    {
        $sistema = $request->attributes->get('sistema_saas');

        $data = $request->validate([
            'referencia_externa' => 'required|string',
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'identificacion_fiscal' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'plan' => 'nullable|string|max:255',
            'monto' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|in:recurrente,unico',
        ]);

        $cuit = Cliente::normalizarCuit($data['identificacion_fiscal'] ?? null);
        $cliente = $this->buscarCliente($cuit, $data['email']);

        if (! $cliente) {
            $cliente = Cliente::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'identificacion_fiscal' => $cuit,
            ]);
        } elseif ($cuit && ! $cliente->identificacion_fiscal) {
            $cliente->update(['identificacion_fiscal' => $cuit]);
        }

        $clienteSistema = ClienteSistema::firstOrNew([
            'id_cliente' => $cliente->id_cliente,
            'id_sistema' => $sistema->id_sistema,
        ]);
        $clienteSistema->fill([
            'referencia_externa' => $data['referencia_externa'],
            'email_alta' => $data['email'],
            'identificacion_fiscal_alta' => $cuit,
        ])->save();

        $suscripcion = null;

        if (! empty($data['plan']) && isset($data['monto']) && ! $clienteSistema->suscripcionActiva()) {
            $suscripcion = Suscripcion::create([
                'id_cliente_sistema' => $clienteSistema->id_cliente_sistema,
                'plan' => $data['plan'],
                'tipo' => $data['tipo'] ?? 'recurrente',
                'monto' => $data['monto'],
                'estado' => 'pendiente',
                'fecha_inicio' => now(),
            ]);
        }

        // La vinculación llama a los otros sistemas: se hace después de responder
        // para no demorar el registro del usuario.
        dispatch(fn () => app(VinculadorContable::class)->evaluar($cliente->fresh()))->afterResponse();

        return response()->json([
            'ok' => true,
            'id_cliente' => $cliente->id_cliente,
            'id_cliente_sistema' => $clienteSistema->id_cliente_sistema,
            'suscripcion_creada' => (bool) $suscripcion,
        ], 201);
    }

    private function buscarCliente(?string $cuit, string $email): ?Cliente
    {
        if ($cuit) {
            // El CUIT puede estar guardado con o sin guiones
            $porCuit = Cliente::whereIn('identificacion_fiscal', [
                $cuit,
                strlen($cuit) === 11 ? substr($cuit, 0, 2) . '-' . substr($cuit, 2, 8) . '-' . substr($cuit, 10) : $cuit,
            ])->first();

            if ($porCuit) {
                return $porCuit;
            }

            // Mismo email, pero solo si ese cliente no es OTRA empresa (otro CUIT)
            return Cliente::where('email', $email)
                ->where(fn ($q) => $q->whereNull('identificacion_fiscal')->orWhere('identificacion_fiscal', ''))
                ->first();
        }

        return Cliente::where('email', $email)->first();
    }
}
