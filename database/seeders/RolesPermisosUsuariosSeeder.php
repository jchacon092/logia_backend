<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesPermisosUsuariosSeeder extends Seeder
{
    public function run(): void
    {
        // Permisos
        $perms = [
            'finanzas.view','finanzas.edit',
            'secretaria.view','secretaria.edit',
            'asistencias.view','asistencias.edit',
            'limosnero.view','limosnero.edit',
            'hospitalario.view','hospitalario.edit',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Roles
        $roles = [
            'superadministrador' => Permission::all()->pluck('name')->toArray(),
            'tesorero'           => ['finanzas.view','finanzas.edit'],
            'venerable'          => ['finanzas.view','secretaria.view','asistencias.view','limosnero.view','hospitalario.view'],
            'general'            => ['finanzas.view','secretaria.view','asistencias.view','limosnero.view','hospitalario.view'],
        ];
        foreach ($roles as $role => $rolePerms) {
            $r = Role::firstOrCreate(['name' => $role]);
            $r->syncPermissions($rolePerms);
        }

        // Usuarios demo
        $users = [
            ['name'=>'Super Admin','email'=>'super@logia.test','role'=>'superadministrador'],
            ['name'=>'Tesorero','email'=>'tesorero@logia.test','role'=>'tesorero'],
            ['name'=>'Venerable','email'=>'venerable@logia.test','role'=>'venerable'],
            ['name'=>'General','email'=>'general@logia.test','role'=>'general'],
        ];
        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email'=>$u['email']],
                ['name'=>$u['name'], 'password'=>Hash::make('secret')]
            );
            $user->syncRoles([$u['role']]);
        }
    }
}
