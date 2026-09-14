<?php

namespace Database\Seeders;

use App\Models\Equipo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EquipoSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('equipos')->truncate();
        Schema::enableForeignKeyConstraints();

        Equipo::factory()->count(61)->create();
    }
}