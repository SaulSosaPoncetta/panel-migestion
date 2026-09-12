<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ClienteSistema;
use App\Models\Suscripcion;
use Illuminate\Http\Request;

class RegistrarClienteController extends Controller
{
    /**
     * POST /api/registrar-cliente
     *
     * Lo llama un sistema satélite (autenticado con su api_key) apenas un
     * usuario nuevo se da de alta y confirma su cuenta, para que el hub
     * lo empiece a controlar automáticamente sin carga manual.
     *
     * Body: { referencia_externa, nombre, email, telefono?, plan?, monto?, tipo? }
     */
    public function __invoke(Request $request)
    {
        $sistema = $request->attributes->get('sistema_saas');

        $data = $request->validate([
            'referencia_externa' => 'required|string',
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'telefono' => 'nullable|string|max:50',
            'plan' => 'nullable|string|max:255',
            'monto' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|in:recurrente,unico',
        ]);

        $cliente = Cliente::firstOrCreate(
            ['email' => $data['email']],
            ['nombre' => $data['nombre'], 'telefono' => $data['telefono'] ?? null]
        );

        $clienteSistema = ClienteSistema::firstOrCreate(
            ['id_cliente' => $cliente->id_cliente, 'id_sistema' => $sistema->id_sistema],
            ['referencia_externa' => $data['referencia_externa']]
        );

        // Si ya existía el vínculo pero con otra referencia (raro, pero por las dudas)
        if ($clienteSistema->referencia_externa !== $data['referencia_externa']) {
            $clienteSistema->update(['referencia_externa' => $data['referencia_externa']]);
        }

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

        return response()->json([
            'ok' => true,
            'id_cliente' => $cliente->id_cliente,
            'id_cliente_sistema' => $clienteSistema->id_cliente_sistema,
            'suscripcion_creada' => (bool) $suscripcion,
        ], 201);
    }
}
