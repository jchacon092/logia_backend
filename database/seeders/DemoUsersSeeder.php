<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // usuario admin de prueba
        $u = User::firstOrCreate(
            ['email' => 'super@logia.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('secret')]
        );

        // Si usas spatie/permission y ya sembraste roles:
        // $u->assignRole('superadministrador');
    }
}
