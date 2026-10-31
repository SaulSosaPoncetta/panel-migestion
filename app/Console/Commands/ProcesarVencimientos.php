<?php

namespace App\Console\Commands;

use App\Mail\AvisoAbonoCliente;
use App\Mail\ResumenAdmin;
use App\Models\Pago;
use App\Models\Suscripcion;
use App\Services\NotificadorSistemaSatelite;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ProcesarVencimientos extends Command
{
    protected $signature = 'panel:procesar-vencimientos {--simular : Muestra qué haría, sin enviar mails ni cambiar nada}';

    protected $description = 'Avisa a los clientes que están por vencer, pasa a "vencida" las que no pagaron y te manda el resumen del día.';

    /** Días extra de margen para clientes con débito automático (el cobro puede demorar). */
    private const MARGEN_DEBITO = 3;

    public function handle(NotificadorSistemaSatelite $notificador): int
    {
        $simular = (bool) $this->option('simular');
        $hoy = Carbon::today(config('pagos.zona_horaria'));
        $hoyStr = $hoy->toDateString();
        $umbrales = collect(config('pagos.avisos_dias'))->map(fn ($d) => (int) $d)->sort()->values();
        $gracia = (int) config('pagos.gracia_dias');

        $this->info(($simular ? '[SIMULACIÓN] ' : '').'Vencimientos al '.$hoy->format('d/m/Y'));

        $resumen = [
            'fecha' => $hoy->format('d/m/Y'),
            'por_vencer' => [],
            'vencidas' => [],
            'transferencias' => [],
            'transferencias_por_revisar' => Pago::where('medio_pago', 'transferencia')
                ->where('estado', 'pendiente')->whereNotNull('comprobante_path')->count(),
        ];

        // 1) Avisos previos al vencimiento
        if ($umbrales->isNotEmpty()) {
            $limite = $hoy->copy()->addDays($umbrales->max())->toDateString();

            $candidatas = Suscripcion::with('clienteSistema.cliente', 'clienteSistema.sistema', 'planCatalogo')
                ->where('estado', 'activa')
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>=', $hoyStr)
                ->whereDate('fecha_vencimiento', '<=', $limite)
                ->orderBy('fecha_vencimiento')
                ->get();

            foreach ($candidatas as $s) {
                $cs = $s->clienteSistema;
                $dias = (int) Carbon::parse($hoyStr)->diffInDays(Carbon::parse($s->fecha_vencimiento->toDateString()), false);
                $debito = $this->tieneDebitoAutomatico($s);

                $resumen['por_vencer'][] = sprintf('%s · %s · vence %s%s', $cs->cliente->nombre, $cs->sistema->nombre,
                    $s->fecha_vencimiento->format('d/m/Y'), $debito ? ' (débito automático)' : '');

                // Con débito automático el cobro es solo: no se le pide que pague
                if ($debito) {
                    continue;
                }

                $umbral = $umbrales->first(fn ($u) => $dias <= $u);

                if ($umbral === null || ($s->aviso_umbral !== null && $umbral >= $s->aviso_umbral)) {
                    continue; // todavía no toca, o ya se avisó para este tramo
                }

                $this->line("  Aviso a {$cs->cliente->email}: {$cs->sistema->nombre} vence en {$dias} día(s)");

                if (! $simular && $this->enviarAviso($s, 'por_vencer', $dias)) {
                    $s->update(['aviso_umbral' => $umbral]);
                }
            }
        }

        // 2) Pasar a "vencida" las que se pasaron de fecha
        $vencidas = Suscripcion::with('clienteSistema.cliente', 'clienteSistema.sistema', 'planCatalogo')
            ->where('estado', 'activa')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', $hoyStr)
            ->get();

        foreach ($vencidas as $s) {
            $cs = $s->clienteSistema;
            $margen = $gracia + ($this->tieneDebitoAutomatico($s) ? self::MARGEN_DEBITO : 0);
            $ultimoDiaValido = Carbon::parse($s->fecha_vencimiento->toDateString())->addDays($margen)->toDateString();

            if ($ultimoDiaValido >= $hoyStr) {
                continue; // dentro del período de gracia
            }

            $this->line("  VENCIDA: {$cs->cliente->nombre} · {$cs->sistema->nombre} (venció el {$s->fecha_vencimiento->format('d/m/Y')})");
            $resumen['vencidas'][] = sprintf('%s · %s · venció el %s', $cs->cliente->nombre, $cs->sistema->nombre, $s->fecha_vencimiento->format('d/m/Y'));

            if ($simular) {
                continue;
            }

            $s->update(['estado' => 'vencida', 'aviso_umbral' => null]);
            $notificador->avisarSuscripcion($s->fresh());
            $this->enviarAviso($s, 'vencido', null);
        }

        // 3) Resumen para vos
        $hayNovedades = $resumen['por_vencer'] || $resumen['vencidas'] || $resumen['transferencias_por_revisar'] > 0;
        $destino = config('pagos.admin_email');

        if ($hayNovedades && $destino) {
            $this->line("Resumen para {$destino}");

            if (! $simular) {
                try {
                    Mail::to($destino)->send(new ResumenAdmin($resumen));
                } catch (\Throwable $e) {
                    Log::warning('No se pudo enviar el resumen diario', ['error' => $e->getMessage()]);
                }
            }
        }

        $this->info(sprintf('Listo: %d por vencer, %d pasaron a vencida, %d transferencia(s) por revisar.',
            count($resumen['por_vencer']), count($resumen['vencidas']), $resumen['transferencias_por_revisar']));

        return self::SUCCESS;
    }

    protected function tieneDebitoAutomatico(Suscripcion $s): bool
    {
        return $s->tipo === 'recurrente' && ! empty($s->mp_preapproval_id);
    }

    protected function enviarAviso(Suscripcion $s, string $tipo, ?int $dias): bool
    {
        $cs = $s->clienteSistema;

        if (! $cs->cliente->email) {
            return false;
        }

        try {
            $url = URL::temporarySignedRoute('pagar.renovar', now()->addDays(30), ['clienteSistema' => $cs->id_cliente_sistema]);
            Mail::to($cs->cliente->email)->send(new AvisoAbonoCliente($s, $tipo, $dias, $url));

            return true;
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el aviso de vencimiento', [
                'cliente' => $cs->cliente->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
