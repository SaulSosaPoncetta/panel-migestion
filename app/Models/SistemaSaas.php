<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SistemaSaas extends Model
{
    use HasFactory;

    protected $table = 'sistemas_saas';
    protected $primaryKey = 'id_sistema';

    protected $fillable = [
        'nombre', 'slug', 'url_base', 'webhook_url', 'api_key', 'webhook_secret', 'activo',
    ];

    protected $hidden = ['api_key', 'webhook_secret'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function clienteSistemas(): HasMany
    {
        return $this->hasMany(ClienteSistema::class, 'id_sistema', 'id_sistema');
    }

    protected static function booted(): void
    {
        static::creating(function (self $sistema) {
            $sistema->api_key ??= (string) str()->uuid();
            $sistema->webhook_secret ??= bin2hex(random_bytes(32));
        });
    }
}
