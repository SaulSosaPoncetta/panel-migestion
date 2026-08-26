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
        'id_cliente_sistema', 'plan', 'tipo', 'monto', 'moneda', 'estado',
        'fecha_inicio', 'proxima_fecha_cobro', 'mp_preapproval_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'proxima_fecha_cobro' => 'date',
        'monto' => 'decimal:2',
    ];

    public function clienteSistema(): BelongsTo
    {
        return $this->belongsTo(ClienteSistema::class, 'id_cliente_sistema', 'id_cliente_sistema');
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
