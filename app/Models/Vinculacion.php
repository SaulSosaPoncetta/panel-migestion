<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Integración automática GestiónComercial → Contabilidad de un mismo cliente.
 */
class Vinculacion extends Model
{
    protected $table = 'vinculaciones';
    protected $primaryKey = 'id_vinculacion';

    public const ESTADOS = [
        'por_confirmar' => ['Por confirmar', 'warning'],
        'vinculada'     => ['Vinculada', 'success'],
        'error'         => ['Error', 'danger'],
        'desvinculada'  => ['Desvinculada', 'secondary'],
    ];

    protected $fillable = [
        'id_cliente', 'id_cs_comercial', 'id_cs_contable', 'estado', 'mensaje', 'vinculada_at',
    ];

    protected $casts = [
        'vinculada_at' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function comercial(): BelongsTo
    {
        return $this->belongsTo(ClienteSistema::class, 'id_cs_comercial', 'id_cliente_sistema');
    }

    public function contable(): BelongsTo
    {
        return $this->belongsTo(ClienteSistema::class, 'id_cs_contable', 'id_cliente_sistema');
    }

    public function etiquetaEstado(): string
    {
        return self::ESTADOS[$this->estado][0] ?? $this->estado;
    }

    public function colorEstado(): string
    {
        return self::ESTADOS[$this->estado][1] ?? 'secondary';
    }
}
