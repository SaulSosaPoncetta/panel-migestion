<?php

use App\Models\Cliente;
use App\Services\VinculadorContable;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Integración automática GestiónComercial → Contabilidad
|--------------------------------------------------------------------------
| php artisan hub:vincular-contable              → revisa todos los clientes (vincula nuevos y reintenta errores)
| php artisan hub:vincular-contable --cliente=5  → solo un cliente
*/
Artisan::command('hub:vincular-contable {--cliente=}', function (VinculadorContable $vinculador) {
    $clientes = Cliente::has('clienteSistemas', '>=', 2)
        ->when($this->option('cliente'), fn ($q, $id) => $q->where('id_cliente', $id))
        ->get();

    foreach ($clientes as $cliente) {
        $v = $vinculador->evaluar($cliente);
        if ($v) {
            $this->line("{$cliente->nombre}: {$v->etiquetaEstado()}" . ($v->mensaje ? " — {$v->mensaje}" : ''));
        }
    }

    $this->info("Listo: {$clientes->count()} cliente(s) revisado(s).");
})->purpose('Vincula GestiónComercial con Contabilidad para los clientes que tienen los dos sistemas');

// Reintenta las vinculaciones con error (requiere el cron de schedule:run en el servidor)
Schedule::command('hub:vincular-contable')->hourly()->withoutOverlapping();
