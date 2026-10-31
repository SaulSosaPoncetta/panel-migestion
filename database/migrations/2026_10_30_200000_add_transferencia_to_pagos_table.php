<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('comprobante_path')->nullable()->after('payload_raw');
            $table->string('comprobante_nombre')->nullable()->after('comprobante_path');
            $table->date('fecha_transferencia')->nullable()->after('comprobante_nombre');
            $table->text('nota_cliente')->nullable()->after('fecha_transferencia');
            $table->timestamp('comprobante_subido_at')->nullable()->after('nota_cliente');
            $table->foreignId('revisado_por')->nullable()->after('comprobante_subido_at')->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable()->after('revisado_por');
            $table->text('motivo_rechazo')->nullable()->after('revisado_at');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revisado_por');
            $table->dropColumn([
                'comprobante_path', 'comprobante_nombre', 'fecha_transferencia', 'nota_cliente',
                'comprobante_subido_at', 'revisado_at', 'motivo_rechazo',
            ]);
        });
    }
};
