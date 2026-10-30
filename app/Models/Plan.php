<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';
    protected $primaryKey = 'id_plan';

    protected $fillable = [
        'id_sistema', 'codigo', 'nombre', 'descripcion', 'tipo', 'duracion_meses',
        'precio', 'moneda', 'cobro_por_unidad', 'unidad_nombre',
        'unidad_minima', 'unidad_maxima', 'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'cobro_por_unidad' => 'boolean',
        'activo' => 'boolean',
    ];

    public function sistema(): BelongsTo
    {
        return $this->belongsTo(SistemaSaas::class, 'id_sistema', 'id_sistema');
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'id_plan', 'id_plan');
    }

    /** Monto a cobrar para la cantidad indicada (fijo, o precio x cantidad si cobra por unidad). */
    public function calcularMonto(int $cantidad = 1): float
    {
        return $this->cobro_por_unidad
            ? round((float) $this->precio * max($cantidad, 1), 2)
            : (float) $this->precio;
    }

    /** Texto del período que cubre cada pago: "Mensual", "Anual", "12 meses", "Sin vencimiento". */
    public function periodoTexto(): string
    {
        return match (true) {
            $this->duracion_meses === null => 'Sin vencimiento',
            $this->duracion_meses === 1 => 'Mensual',
            $this->duracion_meses === 3 => 'Trimestral',
            $this->duracion_meses === 6 => 'Semestral',
            $this->duracion_meses === 12 => 'Anual',
            default => $this->duracion_meses.' meses',
        };
    }
}
