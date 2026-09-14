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
        'cambiado_por_user_id',
        'cambiado_por_nombre',
    ];

    protected $casts = [
        'fecha_reasignacion' => 'datetime',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function cambiadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'cambiado_por_user_id');
    }
}
