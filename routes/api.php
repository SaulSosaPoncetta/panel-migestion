<?php

use App\Http\Controllers\Api\EstadoClienteController;
use App\Http\Controllers\Api\GenerarCobroController;
use App\Http\Controllers\Api\PlanesPublicosController;
use App\Http\Controllers\Api\RegistrarClienteController;
use Illuminate\Support\Facades\Route;

// Precios publicados de un sistema (público, sin clave)
Route::get('/planes-publicos/{slug}', [PlanesPublicosController::class, 'show'])
    ->middleware('throttle:60,1');

Route::middleware('sistema.auth')->group(function () {
    Route::get('/estado-cliente', [EstadoClienteController::class, 'show']);
    Route::post('/generar-cobro', GenerarCobroController::class);
    Route::post('/registrar-cliente', RegistrarClienteController::class);
});
