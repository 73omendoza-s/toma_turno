<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Turno;
use App\Models\Ventanilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TurnoController extends Controller
{
    /**
     * Pantalla de recepción: muestra los servicios que tienen al menos una ventanilla activa.
     */
    public function index()
    {
        $servicios = Servicio::where('activo', true)
            ->whereIn('nombre', Ventanilla::where('activo', true)->select('servicio'))
            ->orderBy('nombre')
            ->get();

        return view('turnos.index', compact('servicios'));
    }

    /**
     * Genera un turno por servicio. El consecutivo se toma de turno_contadores
     * dentro de una transacción con bloqueo, para que dos turnos simultáneos
     * nunca reciban el mismo número.
     */
    public function store(Request $request)
    {
        $request->validate([
            'servicio' => ['required', Rule::exists('servicios', 'nombre')->where('activo', true)],
        ]);

        $servicio = Servicio::where('nombre', $request->servicio)->firstOrFail();

        $ventanilla = Ventanilla::where('servicio', $servicio->nombre)
            ->where('activo', true)
            ->orderBy('numero')
            ->first();

        if (! $ventanilla) {
            return back()->withErrors(['servicio' => 'No hay ventanillas activas para este servicio.']);
        }

        $hoy = today()->toDateString();

        $turno = DB::transaction(function () use ($servicio, $ventanilla, $hoy) {
            DB::table('turno_contadores')->insertOrIgnore([
                'servicio' => $servicio->nombre,
                'fecha' => $hoy,
                'ultimo' => 0,
            ]);

            $contador = DB::table('turno_contadores')
                ->where('servicio', $servicio->nombre)
                ->where('fecha', $hoy)
                ->lockForUpdate()
                ->first();

            $nuevo = $contador->ultimo + 1;

            DB::table('turno_contadores')
                ->where('id', $contador->id)
                ->update(['ultimo' => $nuevo]);

            return Turno::create([
                'numero' => $servicio->prefijo.str_pad((string) $nuevo, 3, '0', STR_PAD_LEFT),
                'servicio' => $servicio->nombre,
                // Provisional: al llamar el turno se sustituye por la ventanilla real
                'ventanilla_id' => $ventanilla->id,
                'estado' => 'pendiente',
                'fecha' => $hoy,
            ]);
        });

        return redirect()->route('turno.ticket', $turno->id);
    }

    /**
     * Ticket imprimible. Recargarlo no genera otro turno.
     */
    public function ticket(Turno $turno)
    {
        abort_unless($turno->created_at->isToday(), 404);

        $enEspera = Turno::where('servicio', $turno->servicio)
            ->where('fecha', today()->toDateString())
            ->where('estado', 'pendiente')
            ->where('id', '<', $turno->id)
            ->count();

        return view('turnos.ticket', compact('turno', 'enEspera'));
    }
}
