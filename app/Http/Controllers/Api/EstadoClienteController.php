<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClienteSistema;
use Illuminate\Http\Request;

class EstadoClienteController extends Controller
{
    /**
     * GET /api/estado-cliente?referencia_externa=123
     * o     /api/estado-cliente?email=cliente@ejemplo.com
     *
     * El sistema satélite llama esto en el login (con caché local de unas
     * horas) para saber si el cliente está al día o hay que bloquearlo.
     * Devuelve lo mismo que el aviso POST {url_base}/api/panel/abono, así el sistema
     * se pone al día aunque el aviso se haya perdido.
     */
    public function show(Request $request)
    {
        $sistema = $request->attributes->get('sistema_saas');

        $request->validate([
            'referencia_externa' => 'required_without:email|string',
            'email' => 'required_without:referencia_externa|email',
        ]);

        $query = ClienteSistema::where('id_sistema', $sistema->id_sistema)
            ->with(['suscripciones' => fn ($q) => $q->latest('id_suscripcion'), 'suscripciones.planCatalogo']);

        if ($request->referencia_externa) {
            $query->where('referencia_externa', $request->referencia_externa);
        } else {
            $query->whereHas('cliente', fn ($q) => $q->where('email', $request->email));
        }

        $clienteSistema = $query->first();

        if (! $clienteSistema) {
            return response()->json(['encontrado' => false, 'estado' => 'desconocido'], 404);
        }

        $suscripcion = $clienteSistema->suscripciones->first();

        return response()->json([
            'encontrado' => true,
            'estado' => $suscripcion?->estado ?? 'sin_suscripcion',
            'plan' => $suscripcion?->plan,
            'plan_codigo' => $suscripcion?->planCatalogo?->codigo,
            'tipo' => $suscripcion?->tipo,
            'cantidad' => $suscripcion ? (int) $suscripcion->cantidad : null,
            'fecha_vencimiento' => $suscripcion?->fecha_vencimiento?->toDateString(),
            'proxima_fecha_cobro' => $suscripcion?->proxima_fecha_cobro,
        ]);
    }
}
