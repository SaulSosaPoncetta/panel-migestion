<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@migestion.com.ar'],
            [
                'name' => 'Saul',
                'password' => bcrypt('password'),
            ]
        );

        if (! $admin->hasRole('superadmin')) {
            $admin->assignRole('superadmin');
        }
    }
}
