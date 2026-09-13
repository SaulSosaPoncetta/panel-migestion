<?php

use App\Http\Controllers\Api\EstadoClienteController;
use App\Http\Controllers\Api\GenerarCobroController;
use App\Http\Controllers\Api\RegistrarClienteController;
use Illuminate\Support\Facades\Route;

Route::middleware('sistema.auth')->group(function () {
    Route::get('/estado-cliente', [EstadoClienteController::class, 'show']);
    Route::post('/generar-cobro', GenerarCobroController::class);
    Route::post('/registrar-cliente', RegistrarClienteController::class);
});
