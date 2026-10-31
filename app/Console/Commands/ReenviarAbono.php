<?php

namespace App\Console\Commands;

use App\Models\Suscripcion;
use App\Services\NotificadorSistemaSatelite;
use Illuminate\Console\Command;

class ReenviarAbono extends Command
{
    protected $signature = 'panel:reenviar-abono {id_suscripcion : id de la suscripción}';

    protected $description = 'Vuelve a enviarle al sistema satélite el estado, vencimiento y cupo de una suscripción.';

    public function handle(NotificadorSistemaSatelite $notificador): int
    {
        $suscripcion = Suscripcion::find($this->argument('id_suscripcion'));

        if (! $suscripcion) {
            $this->error('No existe esa suscripción.');

            return self::FAILURE;
        }

        $notificador->avisarSuscripcion($suscripcion);
        $this->info('Aviso enviado (si el sistema no respondió, queda registrado en storage/logs/laravel.log).');

        return self::SUCCESS;
    }
}
