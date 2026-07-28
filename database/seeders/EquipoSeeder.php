<?php

namespace Database\Seeders;

use App\Models\Equipo;
use Illuminate\Database\Seeder;

class EquipoSeeder extends Seeder
{
    public function run(): void
    {
        Equipo::create([
            'codigo_inventario' => 'INF-NB-001',
            'tipo' => 'Notebook',
            'marca' => 'Lenovo',
            'modelo' => 'ThinkPad L14',
            'numero_serie' => 'LNV12345678',
            'asignado_a' => 'Soporte Terreno',
            'ubicacion' => 'Oficina Central',
            'estado' => 'Excelente',
            'observaciones' => 'Equipo principal asignado'
        ]);

        Equipo::create([
            'codigo_inventario' => 'INF-PC-002',
            'tipo' => 'Desktop',
            'marca' => 'HP',
            'modelo' => 'ProDesk 400 G6',
            'numero_serie' => 'HP98765432',
            'asignado_a' => 'Administración',
            'ubicacion' => 'Pozo Almonte',
            'estado' => 'Bueno',
            'observaciones' => 'Revisión técnica realizada'
        ]);
    }
}