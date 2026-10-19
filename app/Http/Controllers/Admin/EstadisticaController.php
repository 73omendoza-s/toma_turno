<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstadisticaRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class EstadisticaController extends Controller
{
    public function index(EstadisticaRequest $request): View
    {
        $inicio = $request->inicio();
        $fin = $request->fin();

        // Los tiempos ya vienen en segundos (espera_seg / atencion_seg), así el AVG funciona igual en SQLite, PostgreSQL y Oracle.
        $operadores = DB::table('turnos as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->join('ventanillas as v', 'v.id', '=', 't.ventanilla_id')
            ->join('areas as a', 'a.id', '=', 't.area_id')
            ->whereBetween('t.fecha', [$inicio, $fin])
            ->groupBy('u.id', 'u.name', 'a.nombre', 'v.nombre')
            ->selectRaw(
                "u.name as operador, a.nombre as area, v.nombre as ventanilla,
                 SUM(CASE WHEN t.estado = 'finalizado' THEN 1 ELSE 0 END) as atendidos,
                 SUM(CASE WHEN t.estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                 AVG(t.atencion_seg) as prom_atencion,
                 MAX(t.atencion_seg) as max_atencion,
                 AVG(t.espera_seg) as prom_espera"
            )
            ->orderByRaw('SUM(CASE WHEN t.estado = \'finalizado\' THEN 1 ELSE 0 END) DESC')
            ->get();

        $areas = DB::table('turnos as t')
            ->join('areas as a', 'a.id', '=', 't.area_id')
            ->whereBetween('t.fecha', [$inicio, $fin])
            ->groupBy('a.id', 'a.nombre')
            ->selectRaw(
                "a.nombre as area,
                 COUNT(*) as emitidos,
                 SUM(CASE WHEN t.estado = 'finalizado' THEN 1 ELSE 0 END) as atendidos,
                 SUM(CASE WHEN t.estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                 SUM(CASE WHEN t.estado IN ('espera', 'atendiendo') THEN 1 ELSE 0 END) as pendientes,
                 AVG(t.espera_seg) as prom_espera,
                 AVG(t.atencion_seg) as prom_atencion"
            )
            ->orderBy('a.nombre')
            ->get();

        return view('admin.estadisticas', compact('operadores', 'areas', 'inicio', 'fin'));
    }
}
