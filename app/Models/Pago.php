<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'id_suscripcion', 'medio_pago', 'mp_payment_id', 'mp_preference_id',
        'monto', 'moneda', 'estado', 'fecha_pago', 'payload_raw',
        'comprobante_path', 'comprobante_nombre', 'fecha_transferencia', 'nota_cliente',
        'comprobante_subido_at', 'revisado_por', 'revisado_at', 'motivo_rechazo',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
        'payload_raw' => 'array',
        'fecha_transferencia' => 'date',
        'comprobante_subido_at' => 'datetime',
        'revisado_at' => 'datetime',
    ];

    public function suscripcion(): BelongsTo
    {
        return $this->belongsTo(Suscripcion::class, 'id_suscripcion', 'id_suscripcion');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function esTransferencia(): bool
    {
        return $this->medio_pago === 'transferencia';
    }

    /** Código que el cliente pone en el concepto de la transferencia. */
    public function codigoReferencia(): string
    {
        return 'MG-'.str_pad((string) $this->id_pago, 6, '0', STR_PAD_LEFT);
    }

    public function esImagenComprobante(): bool
    {
        return (bool) preg_match('/\.(jpe?g|png)$/i', (string) $this->comprobante_path);
    }
}
