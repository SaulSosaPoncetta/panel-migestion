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
}
