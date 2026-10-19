<?php

namespace App\Http\Controllers;

use App\Models\Cajero;
use App\Models\Turno;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CajeroController extends Controller
{
    private function cajeroActual(): Cajero
    {
        return Cajero::with('ventanilla')->findOrFail(Session::get('cajero_id'));
    }

    private function sinVentanilla(Cajero $cajero)
    {
        $cajero->update(['en_sesion' => false, 'sesion_inicio' => null]);
        Session::forget(['cajero_id', 'cajero_nombre', 'cajero_caja', 'cajero_sesion']);

        return redirect()->route('cajero.login')->withErrors([
            'usuario' => 'Tu usuario no tiene ventanilla asignada. Avisa al supervisor.',
        ]);
    }

    public function index($caja)
    {
        $cajero = $this->cajeroActual();

        if (! $cajero->ventanilla) {
            return $this->sinVentanilla($cajero);
        }

        $hoy = today()->toDateString();

        $turnoActual = Turno::where('cajero_id', $cajero->id)
            ->where('estado', 'atendiendo')
            ->where('fecha', $hoy)
            ->first();

        // Mismo criterio que usa "siguiente": del servicio y del día
        $pendientes = Turno::where('estado', 'pendiente')
            ->where('fecha', $hoy)
            ->where('servicio', $cajero->ventanilla->servicio)
            ->count();

        $atendidosHoy = Turno::where('cajero_id', $cajero->id)
            ->where('estado', 'atendido')
            ->where('fecha', $hoy)
            ->count();

        return view('cajero.index', compact('cajero', 'turnoActual', 'pendientes', 'atendidosHoy'));
    }

    /**
     * Cierra el turno actual y llama al siguiente del mismo servicio.
     * El bloqueo evita que dos ventanillas tomen el mismo turno.
     */
    public function siguiente($caja)
    {
        $cajero = $this->cajeroActual();

        if (! $cajero->ventanilla) {
            return $this->sinVentanilla($cajero);
        }

        DB::transaction(function () use ($cajero) {
            Turno::where('cajero_id', $cajero->id)
                ->where('estado', 'atendiendo')
                ->update(['estado' => 'atendido', 'atendido_at' => now()]);

            $turno = Turno::where('estado', 'pendiente')
                ->where('fecha', today()->toDateString())
                ->where('servicio', $cajero->ventanilla->servicio)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($turno) {
                $ahora = now();
                $turno->update([
                    'estado' => 'atendiendo',
                    'cajero_id' => $cajero->id,
                    'ventanilla_id' => $cajero->ventanilla_id,
                    'llamado_at' => $ahora,
                    'ultimo_llamado_at' => $ahora,
                ]);
            }
        });

        return redirect()->route('cajero.index', $cajero->numero_caja);
    }

    /**
     * Repite el anuncio del turno en atención (la pantalla lo detecta por ultimo_llamado_at).
     */
    public function volverALlamar($caja)
    {
        $cajero = $this->cajeroActual();

        Turno::where('cajero_id', $cajero->id)
            ->where('estado', 'atendiendo')
            ->update(['ultimo_llamado_at' => now()]);

        return redirect()->route('cajero.index', $cajero->numero_caja);
    }

    /**
     * El contribuyente no se presentó: el turno queda cancelado.
     */
    public function noSePresento($caja)
    {
        $cajero = $this->cajeroActual();

        Turno::where('cajero_id', $cajero->id)
            ->where('estado', 'atendiendo')
            ->update(['estado' => 'cancelado']);

        return redirect()->route('cajero.index', $cajero->numero_caja);
    }
}
