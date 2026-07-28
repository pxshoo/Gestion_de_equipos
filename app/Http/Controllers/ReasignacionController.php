<?php

namespace App\Http\Controllers;

use App\Models\Reasignacion;
use Illuminate\Http\Request;

class ReasignacionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = Reasignacion::query();

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('codigo_inventario', 'like', "%{$search}%")
                    ->orWhere('equipo_nombre', 'like', "%{$search}%")
                    ->orWhere('asignado_anterior', 'like', "%{$search}%")
                    ->orWhere('asignado_nuevo', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%");
            });
        }

        $reasignaciones = $query->orderByDesc('fecha_reasignacion')->paginate(20)->withQueryString();

        $totalReasignaciones = Reasignacion::count();
        $ultimaReasignacion = Reasignacion::orderByDesc('fecha_reasignacion')->first();
        $equiposReasignados = Reasignacion::query()->distinct('equipo_id')->count('equipo_id');

        return view('reasignaciones.index', compact(
            'reasignaciones',
            'totalReasignaciones',
            'ultimaReasignacion',
            'equiposReasignados'
        ))->with('search', $search);
    }
}
