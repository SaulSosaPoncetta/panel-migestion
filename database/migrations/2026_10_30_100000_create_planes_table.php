<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id('id_plan');
            $table->foreignId('id_sistema')->constrained('sistemas_saas', 'id_sistema')->cascadeOnDelete();
            $table->string('codigo', 50);                         // Ej: "unipersonal", "institucional"
            $table->string('nombre');                             // Ej: "Docente individual"
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['recurrente', 'unico'])->default('recurrente');
            $table->unsignedSmallInteger('duracion_meses')->nullable(); // meses que cubre cada pago (1 mensual, 12 anual); null = sin vencimiento
            $table->decimal('precio', 12, 2);                     // precio fijo, o precio por unidad si cobro_por_unidad
            $table->string('moneda', 3)->default('ARS');
            $table->boolean('cobro_por_unidad')->default(false);  // true = precio x cantidad (ej: por docente)
            $table->string('unidad_nombre', 50)->nullable();      // Ej: "docente", "usuario"
            $table->unsignedInteger('unidad_minima')->default(1);
            $table->unsignedInteger('unidad_maxima')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_sistema', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
