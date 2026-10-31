<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ClienteSistemaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\PagoAdminController;
use App\Http\Controllers\PagoPublicoController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SistemaSaasController;
use App\Http\Controllers\SuscripcionController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\VinculacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});



Route::get('/home', [HomeController::class, 'index'])->name('dashboard');

// Página pública de pago (se entra con un enlace firmado generado por cada sistema)
Route::middleware('throttle:40,1')->group(function () {
    Route::get('/pagar', [PagoPublicoController::class, 'mostrar'])->name('pagar.mostrar');
    Route::post('/pagar', [PagoPublicoController::class, 'iniciar'])->name('pagar.iniciar');
    Route::get('/pagar/transferencia/{pago}', [TransferenciaController::class, 'mostrar'])->middleware('signed')->name('pagar.transferencia');
    Route::post('/pagar/transferencia/{pago}', [TransferenciaController::class, 'subir'])->middleware('signed')->name('pagar.comprobante');
    // Coinciden con las MP_BACK_URL_* por defecto de config/mercadopago.php
    Route::get('/pagos/{estado}', [PagoPublicoController::class, 'resultado'])
        ->whereIn('estado', ['exito', 'error', 'pendiente'])
        ->name('pagos.resultado');
});

Route::middleware('auth')->group(function () {
    Route::resource('sistemas', SistemaSaasController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('planes', PlanController::class)->parameters(['planes' => 'plan'])->only(['index', 'create', 'store', 'edit', 'update']);

    // Revisión de pagos (transferencias con comprobante y movimientos de Mercado Pago)
    Route::get('registro-pagos', [PagoAdminController::class, 'index'])->name('pagos.index');
    Route::get('registro-pagos/{pago}', [PagoAdminController::class, 'show'])->whereNumber('pago')->name('pagos.show');
    Route::get('registro-pagos/{pago}/comprobante', [PagoAdminController::class, 'comprobante'])->whereNumber('pago')->name('pagos.comprobante');
    Route::post('registro-pagos/{pago}/aprobar', [PagoAdminController::class, 'aprobar'])->whereNumber('pago')->name('pagos.aprobar');
    Route::post('registro-pagos/{pago}/rechazar', [PagoAdminController::class, 'rechazar'])->whereNumber('pago')->name('pagos.rechazar');

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