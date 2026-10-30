<?php

namespace App\Console\Commands;

use App\Models\SistemaSaas;
use App\Services\EnlacePago;
use Illuminate\Console\Command;

class GenerarEnlacePago extends Command
{
    protected $signature = 'panel:enlace-pago
        {slug : slug del sistema (ej: aulavirtual)}
        {ref : id del cliente dentro del sistema}
        {--plan= : código del plan}
        {--cantidad= : cupo contratado (ej: cantidad de docentes)}
        {--email= : email, para dar de alta al cliente si el Panel no lo conoce}
        {--nombre= : nombre, idem}';

    protected $description = 'Genera un enlace de pago firmado (válido 60 minutos) para probar la página de pago.';

    public function handle(): int
    {
        $sistema = SistemaSaas::where('slug', $this->argument('slug'))->first();

        if (! $sistema) {
            $this->error('No existe un sistema con ese slug.');

            return self::FAILURE;
        }

        $url = EnlacePago::generarUrl($sistema, [
            'ref' => $this->argument('ref'),
            'plan' => $this->option('plan'),
            'cantidad' => $this->option('cantidad'),
            'email' => $this->option('email'),
            'nombre' => $this->option('nombre'),
        ]);

        $this->info('Enlace de pago (válido 60 minutos):');
        $this->line($url);

        return self::SUCCESS;
    }
}
