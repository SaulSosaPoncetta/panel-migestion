<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'nombre', 'email', 'telefono', 'identificacion_fiscal', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function clienteSistemas(): HasMany
    {
        return $this->hasMany(ClienteSistema::class, 'id_cliente', 'id_cliente');
    }

    public function vinculaciones(): HasMany
    {
        return $this->hasMany(Vinculacion::class, 'id_cliente', 'id_cliente');
    }

    /** CUIT solo con números (para comparar sin guiones). */
    public static function normalizarCuit(?string $cuit): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $cuit);

        return $digitos !== '' ? $digitos : null;
    }
}
