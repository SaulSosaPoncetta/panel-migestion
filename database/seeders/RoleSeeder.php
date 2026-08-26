<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Este panel es de uso interno tuyo: alcanza con un único rol
        // superadmin, sin matriz de permisos por módulo.
        Role::firstOrCreate(['name' => 'superadmin']);
    }
}
