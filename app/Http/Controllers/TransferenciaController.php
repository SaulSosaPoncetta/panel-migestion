<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Pantallas públicas del pago por transferencia. Se accede con una URL firmada
 * de Laravel (middleware "signed") que genera PagoPublicoController::iniciar.
 */
class TransferenciaController extends Controller
{
    public function mostrar(Pago $pago)
    {
        abort_unless($pago->esTransferencia(), 404);

        $pago->load('suscripcion.clienteSistema.cliente', 'suscripcion.clienteSistema.sistema');

        return view('pagar.transferencia', [
            'pago' => $pago,
            'suscripcion' => $pago->suscripcion,
            'cliente' => $pago->suscripcion->clienteSistema->cliente,
            'sistema' => $pago->suscripcion->clienteSistema->sistema,
            'datos' => config('pagos.transferencia'),
            'urlSubir' => URL::temporarySignedRoute('pagar.comprobante', now()->addDays(7), ['pago' => $pago->id_pago]),
        ]);
    }

    public function subir(Request $request, Pago $pago)
    {
        abort_unless($pago->esTransferencia() && $pago->estado === 'pendiente', 404);

        $request->validate([
            'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'fecha_transferencia' => 'nullable|date|before_or_equal:today',
            'nota' => 'nullable|string|max:500',
        ], [
            'comprobante.required' => 'Adjuntá el comprobante de la transferencia.',
            'comprobante.mimes' => 'El comprobante tiene que ser una imagen (JPG o PNG) o un PDF.',
            'comprobante.max' => 'El comprobante no puede pesar más de 5 MB.',
        ]);

        $archivo = $request->file('comprobante');

        // Archivo privado (storage/app/private): solo se ve desde el Panel con sesión iniciada.
        $ruta = $archivo->store('comprobantes/'.now()->format('Y/m'));

        if ($pago->comprobante_path) {
            Storage::delete($pago->comprobante_path);
        }

        $pago->update([
            'comprobante_path' => $ruta,
            'comprobante_nombre' => $archivo->getClientOriginalName(),
            'fecha_transferencia' => $request->input('fecha_transferencia'),
            'nota_cliente' => $request->input('nota'),
            'comprobante_subido_at' => now(),
        ]);

        return redirect()->to(URL::temporarySignedRoute('pagar.transferencia', now()->addDays(7), ['pago' => $pago->id_pago]))
            ->with('success', 'Recibimos tu comprobante.');
    }
}
