<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vinculación automática entre sistemas de un mismo cliente
 * (GestiónComercial → Contabilidad: las facturas pasan solas a la contabilidad).
 *
 * - Un cliente del hub pasa a ser una EMPRESA (se agrupa por CUIT), por eso el email deja de ser único:
 *   una misma persona puede administrar dos empresas con el mismo email.
 * - Cada cuenta en un sistema guarda con qué email y CUIT se dio de alta, para decidir
 *   si la vinculación es segura (mismo CUIT y mismo email) o la tiene que confirmar el superadmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasIndex('clientes', 'clientes_email_unique')) {
                $table->dropUnique('clientes_email_unique');
            }
            $table->index('email');
            $table->index('identificacion_fiscal');
        });

        Schema::table('cliente_sistemas', function (Blueprint $table) {
            $table->string('email_alta')->nullable()->after('referencia_externa');
            $table->string('identificacion_fiscal_alta', 20)->nullable()->after('email_alta');
        });

        Schema::create('vinculaciones', function (Blueprint $table) {
            $table->id('id_vinculacion');
            $table->foreignId('id_cliente')->constrained('clientes', 'id_cliente')->cascadeOnDelete();
            $table->foreignId('id_cs_comercial')->constrained('cliente_sistemas', 'id_cliente_sistema')->cascadeOnDelete();
            $table->foreignId('id_cs_contable')->constrained('cliente_sistemas', 'id_cliente_sistema')->cascadeOnDelete();
            $table->enum('estado', ['por_confirmar', 'vinculada', 'error', 'desvinculada'])->default('por_confirmar');
            $table->string('mensaje')->nullable();
            $table->timestamp('vinculada_at')->nullable();
            $table->timestamps();

            $table->unique(['id_cs_comercial', 'id_cs_contable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculaciones');

        Schema::table('cliente_sistemas', function (Blueprint $table) {
            $table->dropColumn(['email_alta', 'identificacion_fiscal_alta']);
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['identificacion_fiscal']);
            $table->unique('email');
        });
    }
};
