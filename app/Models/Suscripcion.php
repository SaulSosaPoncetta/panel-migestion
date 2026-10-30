<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';
    protected $primaryKey = 'id_suscripcion';

    protected $fillable = [
        'id_cliente_sistema', 'id_plan', 'plan', 'tipo', 'cantidad', 'monto', 'moneda', 'estado',
        'fecha_inicio', 'proxima_fecha_cobro', 'fecha_vencimiento', 'mp_preapproval_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'proxima_fecha_cobro' => 'date',
        'fecha_vencimiento' => 'date',
        'monto' => 'decimal:2',
    ];

    public function clienteSistema(): BelongsTo
    {
        return $this->belongsTo(ClienteSistema::class, 'id_cliente_sistema', 'id_cliente_sistema');
    }

    public function planCatalogo(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_suscripcion', 'id_suscripcion');
    }

    public function alDia(): bool
    {
        return $this->estado === 'activa';
    }
}
