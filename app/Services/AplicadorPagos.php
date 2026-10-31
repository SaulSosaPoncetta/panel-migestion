<?php

namespace App\Services;

use App\Models\Suscripcion;
use Illuminate\Support\Carbon;

/**
 * Único lugar donde un pago aprobado "se aplica" a una suscripción: calcula el nuevo
 * vencimiento según el plan, deja la suscripción activa y le avisa al sistema satélite.
 * Lo usan el webhook de Mercado Pago y la aprobación manual de transferencias.
 */
class AplicadorPagos
{
    public function __construct(protected NotificadorSistemaSatelite $notificador)
    {
    }

    public function acreditar(Suscripcion $suscripcion): Suscripcion
    {
        $plan = $suscripcion->planCatalogo;

        // Meses que cubre este pago. Sin plan del catálogo: las recurrentes cubren 1 mes
        // y las de pago único quedan sin vencimiento.
        $meses = $plan ? $plan->duracion_meses : ($suscripcion->tipo === 'recurrente' ? 1 : null);

        // Fechas como texto "Y-m-d" en la zona horaria del negocio, para no mezclar husos.
        $hoy = Carbon::today(config('pagos.zona_horaria'))->toDateString();
        $vencimiento = null;

        if ($meses !== null) {
            $actual = $suscripcion->fecha_vencimiento?->toDateString();

            // Si todavía le quedaban días, el nuevo período se suma al final del actual (no pierde días).
            $base = $actual && $actual >= $hoy ? $actual : $hoy;

            $vencimiento = Carbon::parse($base)->addMonthsNoOverflow((int) $meses)->toDateString();
        }

        $suscripcion->update([
            'estado' => 'activa',
            'fecha_inicio' => $suscripcion->fecha_inicio ?? $hoy,
            'fecha_vencimiento' => $vencimiento,
            'proxima_fecha_cobro' => $suscripcion->tipo === 'recurrente' ? $vencimiento : null,
            'aviso_umbral' => null,
        ]);

        $suscripcion = $suscripcion->fresh();
        $this->notificador->avisarSuscripcion($suscripcion);

        return $suscripcion;
    }
}
