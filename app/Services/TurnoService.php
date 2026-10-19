<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoTurno;
use App\Models\Area;
use App\Models\Bitacora;
use App\Models\Turno;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class TurnoService
{
    /** Recepción: emite el siguiente turno del área para hoy, sin duplicar números. */
    public function emitir(int $areaId, User $por): Turno
    {
        $turno = DB::transaction(function () use ($areaId, $por) {
            // Bloquea la fila del área: dos recepciones simultáneas hacen fila aquí.
            $area = Area::whereKey($areaId)->where('activa', true)->lockForUpdate()->firstOrFail();
            $hoy = now()->toDateString();

            $numero = (int) Turno::where('area_id', $area->id)->where('fecha', $hoy)->max('numero') + 1;

            return Turno::create([
                'area_id' => $area->id,
                'creado_por' => $por->id,
                'fecha' => $hoy,
                'numero' => $numero,
                'folio' => $area->prefijo.'-'.str_pad((string) $numero, 3, '0', STR_PAD_LEFT),
                'estado' => EstadoTurno::Espera,
            ]);
        });

        Bitacora::registrar($por->rol->value, $por->id, $por->name, 'TURNO_EMITIDO', "Folio {$turno->folio}");

        return $turno;
    }

    /** Turno que el operador tiene en atención (de cualquier día, por si quedó abierto). */
    public function turnoActual(User $operador): ?Turno
    {
        return Turno::with(['area', 'ventanilla'])
            ->where('user_id', $operador->id)
            ->where('estado', EstadoTurno::Atendiendo->value)
            ->latest('id')
            ->first();
    }

    /** Operador: toma el turno en espera más antiguo de su área. Devuelve null si no hay. */
    public function llamarSiguiente(User $operador): ?Turno
    {
        $ventanilla = $operador->ventanilla()->with('area')->first();
        if (! $ventanilla || ! $ventanilla->activa) {
            throw new DomainException('No tienes una ventanilla activa asignada.');
        }

        $turno = DB::transaction(function () use ($operador, $ventanilla) {
            if ($this->turnoActual($operador)) {
                throw new DomainException('Primero finaliza el turno que estás atendiendo.');
            }

            $turno = Turno::hoy()
                ->where('area_id', $ventanilla->area_id)
                ->where('estado', EstadoTurno::Espera->value)
                ->orderBy('numero')
                ->lockForUpdate()
                ->first();

            if (! $turno) {
                return null;
            }

            $ahora = now();
            $turno->update([
                'estado' => EstadoTurno::Atendiendo,
                'ventanilla_id' => $ventanilla->id,
                'user_id' => $operador->id,
                'llamadas' => 1,
                'llamado_en' => $ahora,
                'ultima_llamada_en' => $ahora,
                'espera_seg' => abs((int) $turno->created_at->diffInSeconds($ahora)),
            ]);

            return $turno;
        });

        if ($turno) {
            Bitacora::registrar(
                $operador->rol->value,
                $operador->id,
                $operador->name,
                'TURNO_LLAMADO',
                "Folio {$turno->folio} en {$ventanilla->nombre}"
            );
        }

        return $turno;
    }

    /** Vuelve a anunciar el turno en la pantalla. */
    public function rellamar(Turno $turno, User $operador): Turno
    {
        $this->validarPropio($turno, $operador);

        $turno->update(['llamadas' => $turno->llamadas + 1, 'ultima_llamada_en' => now()]);
        Bitacora::registrar(
            $operador->rol->value,
            $operador->id,
            $operador->name,
            'TURNO_RELLAMADO',
            "Folio {$turno->folio}, llamada {$turno->llamadas}"
        );

        return $turno;
    }

    /** Termina la atención. Con $ausente = true, el contribuyente no se presentó. */
    public function finalizar(Turno $turno, User $operador, bool $ausente = false): Turno
    {
        $this->validarPropio($turno, $operador);

        $ahora = now();
        $turno->update([
            'estado' => $ausente ? EstadoTurno::Ausente : EstadoTurno::Finalizado,
            'finalizado_en' => $ahora,
            'atencion_seg' => abs((int) $turno->llamado_en->diffInSeconds($ahora)),
        ]);

        Bitacora::registrar(
            $operador->rol->value,
            $operador->id,
            $operador->name,
            $ausente ? 'TURNO_AUSENTE' : 'TURNO_FINALIZADO',
            "Folio {$turno->folio}"
        );

        return $turno;
    }

    private function validarPropio(Turno $turno, User $operador): void
    {
        if ($turno->user_id !== $operador->id || $turno->estado !== EstadoTurno::Atendiendo) {
            throw new DomainException('Ese turno no está en atención por ti.');
        }
    }

    public function generar(Area $area, User $por): Turno
    {
        return $this->emitir($area->id, $por);
    }
}
