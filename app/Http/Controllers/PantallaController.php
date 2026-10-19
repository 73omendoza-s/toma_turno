<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EstadoTurno;
use App\Models\Area;
use App\Models\Turno;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/** Pantalla pública de la sala de espera. Solo expone folios y ventanillas, sin datos personales. */
class PantallaController extends Controller
{
    public function index(): View
    {
        return view('pantalla.index');
    }

    public function datos(): JsonResponse
    {
        $llamados = Turno::hoy()
            ->where('estado', EstadoTurno::Atendiendo->value)
            ->with(['area:id,nombre', 'ventanilla:id,nombre'])
            ->orderByDesc('ultima_llamada_en')
            ->limit(8)
            ->get()
            ->map(fn (Turno $t) => [
                'id' => $t->id,
                'folio' => $t->folio,
                'area' => $t->area->nombre,
                'ventanilla' => $t->ventanilla->nombre,
                'llamada' => $t->ultima_llamada_en->getTimestamp(),
            ])
            ->values();

        $espera = Area::query()
            ->where('activa', true)
            ->orderBy('orden')->orderBy('nombre')
            ->withCount(['turnos as en_espera' => fn ($q) => $q->hoy()->where('estado', EstadoTurno::Espera->value)])
            ->get()
            ->map(fn (Area $a) => ['area' => $a->nombre, 'en_espera' => $a->en_espera])
            ->values();

        return response()->json(['llamados' => $llamados, 'espera' => $espera]);
    }
}
