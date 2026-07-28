<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use App\Models\Reasignacion;
use Illuminate\Console\Command;

class BackfillReasignacionesCommand extends Command
{
    protected $signature = 'reasignaciones:backfill {--fresh : Elimina y vuelve a generar los registros existentes}';
    protected $description = 'Crea registros de historial para equipos que ya tenían una reasignación cargada antes de existir el módulo de historial';

    public function handle(): int
    {
        $equipos = Equipo::whereNotNull('equipo_reasignado_a')
            ->where('equipo_reasignado_a', '!=', '')
            ->get();

        $creados = 0;

        foreach ($equipos as $equipo) {
            if ($this->option('fresh')) {
                Reasignacion::where('equipo_id', $equipo->id)->delete();
            }

            $yaExiste = Reasignacion::where('equipo_id', $equipo->id)->exists();

            if ($yaExiste) {
                continue;
            }

            Reasignacion::create([
                'equipo_id' => $equipo->id,
                'codigo_inventario' => $equipo->codigo_inventario,
                'equipo_nombre' => $equipo->nombre,
                'tipo' => $equipo->tipo,
                'marca' => $equipo->marca,
                'modelo' => $equipo->modelo,
                // "nombre" es el campo con el nombre real y actual de la persona asignada.
                // "equipo_reasignado_a" indica a quién se le reasignó el equipo.
                'asignado_anterior' => null,
                'asignado_nuevo' => $equipo->nombre,
                'equipo_reasignado_anterior' => null,
                'equipo_reasignado_nuevo' => $equipo->equipo_reasignado_a,
                'ubicacion' => $equipo->ubicacion,
                'fecha_reasignacion' => $equipo->updated_at ?? $equipo->created_at ?? now(),
            ]);

            $creados++;
        }

        $this->info("Registros de historial creados: {$creados}");

        return 0;
    }
}
