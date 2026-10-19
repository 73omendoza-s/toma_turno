<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\EstadoTurno;
use App\Livewire\Admin\RegistraBitacora;
use App\Models\Configuracion;
use App\Models\Turno;
use App\Services\TurnoService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Ventanilla')]
class Operador extends Component
{
    use RegistraBitacora;

    // ---- AJUSTA si la columna con la hora en que se llamó el turno se llama distinto ----
    private const COL_INICIO_ATENCION = 'llamado_at';
    // -------------------------------------------------------------------------------------

    public ?string $mensaje = null;

    public function llamar(TurnoService $servicio): void
    {
        $this->mensaje = null;

        try {
            if (! $servicio->llamarSiguiente(auth()->user())) {
                $this->mensaje = 'No hay turnos en espera.';
            }
        } catch (DomainException $e) {
            $this->mensaje = $e->getMessage();
        }
    }

    public function rellamar(TurnoService $servicio): void
    {
        $this->accion(fn ($turno) => $servicio->rellamar($turno, auth()->user()), $servicio);
    }

    public function finalizar(TurnoService $servicio, bool $ausente = false): void
    {
        $this->accion(fn ($turno) => $servicio->finalizar($turno, auth()->user(), $ausente), $servicio);
    }

    /** Finaliza el turno actual y llama de inmediato al siguiente. */
    public function finalizarYSiguiente(TurnoService $servicio): void
    {
        $this->finalizar($servicio);
        if ($this->mensaje === null) {
            $this->llamar($servicio);
        }
    }

    /** El operador indica que necesita más tiempo: solo se deja constancia en la bitácora. */
    public function masTiempo(TurnoService $servicio): void
    {
        $turno = $servicio->turnoActual(auth()->user());
        if ($turno) {
            $this->registrar('tiempo_extendido', "Turno {$turno->folio}: el operador indicó que necesita más tiempo");
        }
    }

    private function accion(callable $fn, TurnoService $servicio): void
    {
        $this->mensaje = null;

        try {
            $turno = $servicio->turnoActual(auth()->user());
            if (! $turno) {
                throw new DomainException('No tienes un turno en atención.');
            }
            $fn($turno);
        } catch (DomainException $e) {
            $this->mensaje = $e->getMessage();
        }
    }

    public function render(TurnoService $servicio)
    {
        $user = auth()->user();
        $ventanilla = $user->ventanilla()->with('area')->first();
        $turno = $servicio->turnoActual($user);

        // Segundos que lleva el turno en atención (el navegador sigue contando desde este valor)
        $transcurridoSeg = 0;
        if ($turno && $turno->{self::COL_INICIO_ATENCION}) {
            $inicio = Carbon::parse($turno->{self::COL_INICIO_ATENCION});
            $transcurridoSeg = max(0, (int) $inicio->diffInSeconds(now()));
        }

        // Resumen del día de este operador (por la columna fecha; query builder para no pasar por los casts)
        $conteos = DB::table('turnos')
            ->where('fecha', today()->toDateString())
            ->where('user_id', $user->id)
            ->selectRaw('estado, COUNT(*) AS n')
            ->groupBy('estado')
            ->pluck('n', 'estado');

        $resumen = [
            'proceso' => (int) ($conteos[EstadoTurno::Atendiendo->value] ?? 0),
            'finalizados' => (int) ($conteos[EstadoTurno::Finalizado->value] ?? 0),
            'ausentes' => (int) ($conteos[EstadoTurno::Ausente->value] ?? 0),
        ];

        return view('livewire.operador', [
            'ventanilla' => $ventanilla,
            'turno' => $turno,
            'enEspera' => $ventanilla
                ? Turno::hoy()->enEspera()->deArea($ventanilla->area_id)->count()
                : 0,
            'transcurridoSeg' => $transcurridoSeg,
            'limiteSeg' => Configuracion::entero('tiempo_atencion_min', 15) * 60,
            'extensionSeg' => Configuracion::entero('tiempo_extension_min', 5) * 60,
            'resumen' => $resumen,
        ]);
    }
}
