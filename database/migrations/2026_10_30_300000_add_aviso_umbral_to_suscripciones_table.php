<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suscripciones', function (Blueprint $table) {
            // Último aviso de vencimiento enviado para el vencimiento actual (7, 3 o 1 días). Se reinicia al pagar.
            $table->unsignedSmallInteger('aviso_umbral')->nullable()->after('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::table('suscripciones', function (Blueprint $table) {
            $table->dropColumn('aviso_umbral');
        });
    }
};
