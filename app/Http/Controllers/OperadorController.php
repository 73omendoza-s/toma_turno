<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EstadoTurno;
use App\Models\Turno;
use App\Services\TurnoService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperadorController extends Controller
{
    public function __construct(private readonly TurnoService $turnos) {}

    public function panel(Request $request): View
    {
        $operador = $request->user()->load('ventanilla.area');

        return view('operador.panel', [
            'operador' => $operador,
            'actual' => $this->turnos->turnoActual($operador),
            'atendidosHoy' => $this->contarHoy($operador->id, EstadoTurno::Finalizado),
            'ausentesHoy' => $this->contarHoy($operador->id, EstadoTurno::Ausente),
            'enEspera' => $this->cola($request)->count(),
        ]);
    }

    public function llamar(Request $request): RedirectResponse
    {
        try {
            $turno = $this->turnos->llamarSiguiente($request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return $turno === null
            ? back()->with('error', 'No hay turnos en espera para tu área.')
            : back()->with('ok', "Llamando al turno {$turno->folio}.");
    }

    public function finalizar(Request $request, Turno $turno): RedirectResponse
    {
        try {
            $this->turnos->finalizar($request->user(), $turno);

            if ($request->boolean('siguiente')) {
                $siguiente = $this->turnos->llamarSiguiente($request->user());

                return $siguiente === null
                    ? back()->with('ok', "Turno {$turno->folio} finalizado. No hay más turnos en espera.")
                    : back()->with('ok', "Turno {$turno->folio} finalizado. Llamando al {$siguiente->folio}.");
            }
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('ok', "Turno {$turno->folio} finalizado.");
    }

    public function ausente(Request $request, Turno $turno): RedirectResponse
    {
        try {
            $this->turnos->marcarAusente($request->user(), $turno);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('ok', "Turno {$turno->folio} marcado como no presentado.");
    }

    public function rellamar(Request $request, Turno $turno): RedirectResponse
    {
        try {
            $this->turnos->rellamar($request->user(), $turno);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('ok', "Se volvió a llamar al turno {$turno->folio}.");
    }

    /** Turnos en espera del área de la ventanilla del operador (solo hoy). */
    private function cola(Request $request): Builder
    {
        return Turno::hoy()
            ->where('area_id', $request->user()->ventanilla?->area_id)
            ->where('estado', EstadoTurno::Espera->value);
    }

    /** Lo consulta el panel cada pocos segundos para refrescar la cola sin recargar. */
    public function colaJson(Request $request): JsonResponse
    {
        $consulta = $this->cola($request);

        return response()->json([
            'en_espera' => (clone $consulta)->count(),
            'proximos' => (clone $consulta)->orderBy('numero')->limit(5)->pluck('folio'),
        ]);
    }

    private function contarHoy(int $usuarioId, EstadoTurno $estado): int
    {
        return Turno::hoy()->where('user_id', $usuarioId)->where('estado', $estado->value)->count();
    }
}
