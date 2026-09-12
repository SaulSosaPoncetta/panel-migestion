<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClienteSistema;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;

class GenerarCobroController extends Controller
{
    /**
     * POST /api/generar-cobro
     * Body: { "referencia_externa": "123" }
     *
     * A diferencia de /suscripciones/{id}/cobrar (que usa el panel web con
     * tu login de superadmin), este endpoint lo llama el propio sistema
     * satélite —autenticado con su api_key— cuando el usuario final hace
     * clic en "Pagar" y necesita el link de Mercado Pago al toque.
     */
    public function __invoke(Request $request, MercadoPagoService $mercadoPago)
    {
        $sistema = $request->attributes->get('sistema_saas');

        $request->validate(['referencia_externa' => 'required|string']);

        $clienteSistema = ClienteSistema::where('id_sistema', $sistema->id_sistema)
            ->where('referencia_externa', $request->referencia_externa)
            ->with('suscripciones')
            ->first();

        if (! $clienteSistema) {
            return response()->json(['error' => 'cliente no encontrado'], 404);
        }

        $suscripcion = $clienteSistema->suscripciones()
            ->whereIn('estado', ['pendiente', 'vencida', 'activa'])
            ->latest('id_suscripcion')
            ->first();

        if (! $suscripcion) {
            return response()->json(['error' => 'no tiene una suscripción cargada'], 404);
        }

        if ($suscripcion->tipo === 'recurrente') {
            if ($suscripcion->mp_preapproval_id) {
                $datos = $mercadoPago->obtenerPreapproval($suscripcion->mp_preapproval_id);
                if (($datos['status'] ?? null) === 'pending') {
                    return response()->json(['link_pago' => $datos['init_point']]);
                }
            }
            $resultado = $mercadoPago->crearPreapproval($suscripcion);
            $suscripcion->update(['mp_preapproval_id' => $resultado['id']]);

            return response()->json(['link_pago' => $resultado['init_point']]);
        }

        $resultado = $mercadoPago->crearPreferencia($suscripcion);

        return response()->json(['link_pago' => $resultado['init_point']]);
    }
}
