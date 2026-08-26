<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClienteSistema extends Model
{
    use HasFactory;

    protected $table = 'cliente_sistemas';
    protected $primaryKey = 'id_cliente_sistema';

    protected $fillable = [
        'id_cliente', 'id_sistema', 'referencia_externa', 'fecha_alta',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function sistema(): BelongsTo
    {
        return $this->belongsTo(SistemaSaas::class, 'id_sistema', 'id_sistema');
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'id_cliente_sistema', 'id_cliente_sistema');
    }

    public function suscripcionActiva(): ?Suscripcion
    {
        return $this->suscripciones()
            ->whereIn('estado', ['activa', 'pendiente'])
            ->latest('id_suscripcion')
            ->first();
    }
}
