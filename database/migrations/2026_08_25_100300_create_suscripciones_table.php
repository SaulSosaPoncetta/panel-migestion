<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id('id_suscripcion');
            $table->foreignId('id_cliente_sistema')->constrained('cliente_sistemas', 'id_cliente_sistema')->cascadeOnDelete();
            $table->string('plan')->nullable();               // Ej: "Mensual", "Anual", "Único"
            $table->enum('tipo', ['recurrente', 'unico']);
            $table->decimal('monto', 12, 2);
            $table->string('moneda', 3)->default('ARS');
            $table->enum('estado', ['pendiente', 'activa', 'vencida', 'suspendida', 'cancelada'])->default('pendiente');
            $table->date('fecha_inicio')->nullable();
            $table->date('proxima_fecha_cobro')->nullable();  // solo aplica a recurrentes
            $table->string('mp_preapproval_id')->nullable();   // id de suscripción en Mercado Pago (recurrente)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
