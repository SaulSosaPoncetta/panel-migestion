<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sistemas_saas', function (Blueprint $table) {
            $table->id('id_sistema');
            $table->string('nombre');                     // Ej: "GestiónAula", "GestiónComercial"
            $table->string('slug')->unique();              // Ej: "gestionaula", "gestioncomercial"
            $table->string('url_base');                    // Ej: https://gestionaula.migestion.com.ar
            $table->string('webhook_url')->nullable();     // Endpoint del sistema satélite que recibe el aviso de cambio de estado
            $table->string('api_key')->unique();            // Key que usa el sistema satélite para consultar el hub
            $table->string('webhook_secret');               // Secret para firmar el payload del webhook saliente
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sistemas_saas');
    }
};
