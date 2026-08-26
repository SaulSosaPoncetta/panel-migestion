<?php

namespace App\Http\Controllers;

use App\Models\ClienteSistema;
use App\Models\Suscripcion;
use App\Services\MercadoPagoService;
use App\Services\NotificadorSistemaSatelite;
use Illuminate\Http\Request;

class SuscripcionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    public function store(Request $request, ClienteSistema $clienteSistema)
    {
        $data = $request->validate([
            'plan' => 'required|string|max:255',
            'tipo' => 'required|in:recurrente,unico',
            'monto' => 'required|numeric|min:1',
            'moneda' => 'nullable|string|size:3',
        ]);

        $suscripcion = Suscripcion::create([
            'id_cliente_sistema' => $clienteSistema->id_cliente_sistema,
            'plan' => $data['plan'],
            'tipo' => $data['tipo'],
            'monto' => $data['monto'],
            'moneda' => $data['moneda'] ?? 'ARS',
            'estado' => 'pendiente',
            'fecha_inicio' => now(),
        ]);

        return redirect()->route('clientes.show', $clienteSistema->id_cliente)
            ->with('success', 'Suscripción creada. Generá el link de cobro para enviárselo al cliente.');
    }

    /**
     * Genera el link de pago en Mercado Pago (preferencia o preapproval según el tipo)
     * y redirige al superadmin a Mercado Pago para copiar/compartir el link, o lo muestra en pantalla.
     */
    public function generarCobro(Suscripcion $suscripcion, MercadoPagoService $mercadoPago)
    {
        if ($suscripcion->tipo === 'recurrente') {
            $resultado = $mercadoPago->crearPreapproval($suscripcion);
            $suscripcion->update(['mp_preapproval_id' => $resultado['id']]);
            $linkPago = $resultado['init_point'];
        } else {
            $resultado = $mercadoPago->crearPreferencia($suscripcion);
            $linkPago = $resultado['init_point'];
        }

        return back()->with('success', 'Link de cobro generado.')->with('link_pago', $linkPago);
    }

    /**
     * Cambio manual de estado (ej: suspender por falta de pago detectada a mano,
     * o reactivar después de un pago que llegó por fuera de MP).
     */
    public function cambiarEstado(
        Request $request,
        Suscripcion $suscripcion,
        NotificadorSistemaSatelite $notificador
    ) {
        $data = $request->validate([
            'estado' => 'required|in:activa,vencida,suspendida,cancelada',
        ]);

        $suscripcion->update(['estado' => $data['estado']]);
        $notificador->avisarCambioEstado($suscripcion->clienteSistema, $data['estado']);

        return back()->with('success', 'Estado actualizado y sistema satélite notificado.');
    }
}
