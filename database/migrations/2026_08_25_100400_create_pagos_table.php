<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->foreignId('id_suscripcion')->constrained('suscripciones', 'id_suscripcion')->cascadeOnDelete();
            $table->string('medio_pago')->default('mercadopago'); // preparado para sumar otros medios a futuro
            $table->string('mp_payment_id')->nullable()->index();
            $table->string('mp_preference_id')->nullable();
            $table->decimal('monto', 12, 2);
            $table->string('moneda', 3)->default('ARS');
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'reembolsado'])->default('pendiente');
            $table->timestamp('fecha_pago')->nullable();
            $table->json('payload_raw')->nullable(); // respuesta cruda de MP, útil para debug
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
