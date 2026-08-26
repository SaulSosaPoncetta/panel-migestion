<?php

use App\Http\Controllers\Api\EstadoClienteController;
use Illuminate\Support\Facades\Route;

Route::middleware('sistema.auth')->group(function () {
    Route::get('/estado-cliente', [EstadoClienteController::class, 'show']);
});
