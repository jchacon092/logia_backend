<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TesoreriaSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categorías de egreso ──────────────────────────────────────────────
        $categorias = [
            'Conserje',
            'Energía eléctrica',
            'Capitaciones Gran Logia',
            'Derechos de iniciación',
            'Derechos de exaltación',
            'Derechos de aumento de salario',
            'Ágape',
            'Materiales y reparaciones',
            'Transporte paramentos',
            'Artículos de limpieza',
            'Otros',
        ];

        $now = now();
        foreach ($categorias as $nombre) {
            DB::table('categorias_egreso')->insertOrIgnore([
                'nombre'     => $nombre,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ── Permisos ──────────────────────────────────────────────────────────
        $permisos = ['tesoreria.view', 'tesoreria.edit'];

        foreach ($permisos as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }

        // ── Asignar a roles ───────────────────────────────────────────────────
        $rolesConAcceso = ['superadministrador', 'tesorero'];

        foreach ($rolesConAcceso as $rolNombre) {
            $rol = Role::where('name', $rolNombre)->first();
            if ($rol) {
                $rol->givePermissionTo($permisos);
            }
        }

        // Tesorero solo puede ver (no editar) — ajusta si quieres que también edite
        // Si quieres que tesorero SOLO vea:
        // $tesorero = Role::where('name','tesorero')->first();
        // if ($tesorero) $tesorero->givePermissionTo('tesoreria.view');

        $this->command->info('TesoreriaSeeder ejecutado: categorías + permisos OK.');
    }
}
