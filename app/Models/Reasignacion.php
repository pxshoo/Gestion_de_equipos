<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reasignacion extends Model
{
    use HasFactory;

    protected $table = 'reasignaciones';

    protected $fillable = [
        'equipo_id',
        'codigo_inventario',
        'equipo_nombre',
        'tipo',
        'marca',
        'modelo',
        'asignado_anterior',
        'asignado_nuevo',
        'equipo_reasignado_anterior',
        'equipo_reasignado_nuevo',
        'ubicacion',
        'fecha_reasignacion',
    ];

    protected $casts = [
        'fecha_reasignacion' => 'datetime',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}
