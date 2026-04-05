<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MiembrosSeeder extends Seeder
{
    public function run(): void
    {
        // Permiso para gestionar miembros y usuarios
        $permiso = Permission::firstOrCreate([
            'name'       => 'miembros.manage',
            'guard_name' => 'web',
        ]);

        // Asignar a superadministrador y venerable (secretario usa el rol venerable o general)
        $roles = ['superadministrador', 'venerable'];

        foreach ($roles as $rolNombre) {
            $rol = Role::where('name', $rolNombre)->first();
            if ($rol) {
                $rol->givePermissionTo($permiso);
            }
        }

        $this->command->info('MiembrosSeeder: permiso miembros.manage asignado OK.');
    }
}
