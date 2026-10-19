<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EstadoTurno;
use App\Models\Area;
use App\Models\Turno;
use App\Services\TurnoService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class RecepcionController extends Controller
{
    public function index(): View
    {
        $areas = Area::query()
            ->where('activa', true)
            ->orderBy('orden')->orderBy('nombre')
            ->with(['ventanillas' => fn ($q) => $q->where('activa', true)->orderBy('id')])
            ->withCount(['turnos as en_espera' => fn ($q) => $q->hoy()->where('estado', EstadoTurno::Espera->value)])
            ->get();

        $recientes = Turno::with('area')->hoy()->latest('id')->limit(10)->get();

        return view('recepcion.index', compact('areas', 'recientes'));
    }

    public function store(Request $request, Area $area, TurnoService $turnos): RedirectResponse
    {
        try {
            $turno = $turnos->generar($area, $request->user());
        } catch (DomainException|RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('recepcion.turnos.show', $turno);
    }

    public function show(Turno $turno): View
    {
        $turno->load(['area.ventanillas' => fn ($q) => $q->where('activa', true)->orderBy('id')]);

        $adelante = Turno::hoy()
            ->where('area_id', $turno->area_id)
            ->where('estado', EstadoTurno::Espera->value)
            ->where('numero', '<', $turno->numero)
            ->count();

        return view('recepcion.ticket', compact('turno', 'adelante'));
    }
}
