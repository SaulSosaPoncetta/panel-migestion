<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Una fila por cada alta de un cliente en uno de tus sistemas SaaS.
        // "referencia_externa" guarda el id del usuario/empresa en el sistema satélite,
        // para que el webhook/API sepa a quién avisarle.
        Schema::create('cliente_sistemas', function (Blueprint $table) {
            $table->id('id_cliente_sistema');
            $table->foreignId('id_cliente')->constrained('clientes', 'id_cliente')->cascadeOnDelete();
            $table->foreignId('id_sistema')->constrained('sistemas_saas', 'id_sistema')->cascadeOnDelete();
            $table->string('referencia_externa')->nullable(); // id_empresa o id_usuario en el sistema satélite
            $table->timestamp('fecha_alta')->useCurrent();
            $table->timestamps();

            $table->unique(['id_cliente', 'id_sistema']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_sistemas');
    }
};
