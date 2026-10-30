<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suscripciones', function (Blueprint $table) {
            $table->foreignId('id_plan')->nullable()->after('id_cliente_sistema')
                ->constrained('planes', 'id_plan')->nullOnDelete();
            $table->unsignedInteger('cantidad')->default(1)->after('tipo');   // cupo contratado (ej: cantidad de docentes)
            $table->date('fecha_vencimiento')->nullable()->after('proxima_fecha_cobro'); // hasta cuándo está pago el servicio
        });
    }

    public function down(): void
    {
        Schema::table('suscripciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_plan');
            $table->dropColumn(['cantidad', 'fecha_vencimiento']);
        });
    }
};
