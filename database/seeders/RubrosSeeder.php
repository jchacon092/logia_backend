<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rubro;           // asegúrate de tener este modelo

class RubrosSeeder extends Seeder
{
    public function run(): void
    {
        $rubros = [
            'Mantenimiento (Josias)',
            'Gran Logia',
            'Luz/Agua',
            'Proyecto',
            'Mantenimiento Logia',
        ];

        foreach ($rubros as $nombre) {
            Rubro::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
