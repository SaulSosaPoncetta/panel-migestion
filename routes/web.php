<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ClienteSistemaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\SistemaSaasController;
use App\Http\Controllers\SuscripcionController;
use App\Http\Controllers\VinculacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});



Route::get('/home', [HomeController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('sistemas', SistemaSaasController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('clientes', ClienteController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('clientes/{cliente}/sistemas', [ClienteSistemaController::class, 'store'])->name('clientes.sistemas.store');
    Route::delete('clientes/{cliente}/sistemas/{clienteSistema}', [ClienteSistemaController::class, 'destroy'])->name('clientes.sistemas.destroy');

    Route::post('cliente-sistemas/{clienteSistema}/suscripciones', [SuscripcionController::class, 'store'])->name('suscripciones.store');
    Route::post('suscripciones/{suscripcion}/cobrar', [SuscripcionController::class, 'generarCobro'])->name('suscripciones.cobrar');
    Route::post('suscripciones/{suscripcion}/estado', [SuscripcionController::class, 'cambiarEstado'])->name('suscripciones.estado');

    // Integración automática GestiónComercial → Contabilidad
    Route::post('clientes/{cliente}/vinculaciones/revisar', [VinculacionController::class, 'revisar'])->name('vinculaciones.revisar');
    Route::post('vinculaciones/{vinculacion}/vincular', [VinculacionController::class, 'vincular'])->name('vinculaciones.vincular');
    Route::post('vinculaciones/{vinculacion}/desvincular', [VinculacionController::class, 'desvincular'])->name('vinculaciones.desvincular');
});

// Webhook público (Mercado Pago no manda cookies de sesión ni CSRF token)
Route::post('/webhooks/mercadopago', MercadoPagoWebhookController::class)->name('webhooks.mercadopago');

require __DIR__.'/auth.php';