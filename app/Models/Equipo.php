<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $fillable = [
        'categoria',
        'codigo_inventario',
        'tipo',
        'marca',
        'modelo',
        'numero_serie',
        'nombre',
        'usuario_pc',
        'procesador',
        'tipo_disco_duro',
        'ram_instalada',
        'pantalla_externa',
        'marca_monitor',
        'modelo_monitor',
        'numero_serie_monitor',
        'teclado',
        'mouse',
        'base_notebook',
        'onedrive_funcionando',
        'respaldo_onedrive',
        'asignado_a',
        'equipo_reasignado_a',
        'ubicacion',
        'valoracion_equipo',
        'valoracion_monitor',
        'valoracion_equipo_actual',
        'valoracion_equipo_actual_numero',
        'mantencion_realizada',
        'estado',
        'observaciones',
    ];
}
