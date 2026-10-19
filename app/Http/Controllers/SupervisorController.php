<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Cajero;
use App\Models\PantallaContenido;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\Ventanilla;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SupervisorController extends Controller
{
    private function auditar(string $accion, ?string $detalle = null): void
    {
        Bitacora::registrar('supervisor', Session::get('supervisor_id'), Session::get('supervisor_nombre'), $accion, $detalle);
    }

    public function index()
    {
        $hoy = today()->toDateString();

        $cajeros = Cajero::with('ventanilla')->withCount([
            'turnos as atendidos' => fn ($q) => $q->where('estado', 'atendido')->where('fecha', $hoy),
        ])->get();

        $ventanillas = Ventanilla::orderBy('servicio')->orderBy('numero')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $contenidos = PantallaContenido::latest()->get();

        $totalAtendidos = Turno::where('estado', 'atendido')->where('fecha', $hoy)->count();
        $totalPendientes = Turno::where('estado', 'pendiente')->where('fecha', $hoy)->count();
        $totalCancelados = Turno::where('estado', 'cancelado')->where('fecha', $hoy)->count();

        // En segundos: en minutos enteros las atenciones cortas daban 0
        $tiempoPromedio = Turno::whereNotNull('llamado_at')
            ->whereNotNull('atendido_at')
            ->where('estado', 'atendido')
            ->where('fecha', $hoy)
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, llamado_at, atendido_at)) as promedio')
            ->value('promedio');

        return view('supervisor.index', compact(
            'cajeros',
            'ventanillas',
            'servicios',
            'contenidos',
            'totalAtendidos',
            'totalPendientes',
            'totalCancelados',
            'tiempoPromedio'
        ));
    }

    public function create()
    {
        $ventanillas = Ventanilla::where('activo', true)->orderBy('servicio')->orderBy('numero')->get();

        return view('supervisor.cajero_form', compact('ventanillas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'max:100'],
            'clave_empleado' => ['required', 'integer', 'unique:cajeros'],
            'ventanilla_id' => ['required', 'exists:ventanillas,id'],
            'usuario' => ['required', 'max:50', 'unique:cajeros'],
            'password' => ['required', 'min:6'],
        ]);

        $ventanilla = Ventanilla::findOrFail($request->ventanilla_id);

        try {
            Cajero::create([
                'nombre' => mb_strtoupper($request->nombre),
                'clave_empleado' => $request->clave_empleado,
                // El puesto físico es el nombre de la ventanilla (una sola fuente de verdad)
                'numero_caja' => $ventanilla->nombre,
                'ventanilla_id' => $ventanilla->id,
                'usuario' => $request->usuario,
                'password' => Hash::make($request->password),
                'activo' => true,
                'en_sesion' => false,
            ]);
        } catch (QueryException $e) {
            return back()->withInput()->withErrors([
                'ventanilla_id' => 'No se pudo guardar. Si la ventanilla ya tiene un cajero, revisa el índice único de numero_caja (ver README).',
            ]);
        }

        $this->auditar('crear_cajero', $request->usuario.' → '.$ventanilla->nombre);

        return redirect()->route('supervisor.index')
            ->with('success', 'Cajero creado correctamente.');
    }

    public function edit($id)
    {
        $cajero = Cajero::findOrFail($id);
        $ventanillas = Ventanilla::where('activo', true)->orderBy('servicio')->orderBy('numero')->get();

        return view('supervisor.cajero_form', compact('cajero', 'ventanillas'));
    }

    public function update(Request $request, $id)
    {
        $cajero = Cajero::findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'max:100'],
            'clave_empleado' => ['required', 'integer', 'unique:cajeros,clave_empleado,'.$id],
            'ventanilla_id' => ['required', 'exists:ventanillas,id'],
            'usuario' => ['required', 'max:50', 'unique:cajeros,usuario,'.$id],
            'password' => ['nullable', 'min:6'],
        ]);

        $ventanilla = Ventanilla::findOrFail($request->ventanilla_id);

        $data = [
            'nombre' => mb_strtoupper($request->nombre),
            'clave_empleado' => $request->clave_empleado,
            'numero_caja' => $ventanilla->nombre,
            'ventanilla_id' => $ventanilla->id,
            'usuario' => $request->usuario,
            'activo' => $request->has('activo'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        try {
            $cajero->update($data);
        } catch (QueryException $e) {
            return back()->withInput()->withErrors([
                'ventanilla_id' => 'No se pudo guardar. Revisa el índice único de numero_caja (ver README).',
            ]);
        }

        $this->auditar('editar_cajero', $cajero->usuario.($request->filled('password') ? ' (cambió contraseña)' : ''));

        return redirect()->route('supervisor.index')
            ->with('success', 'Cajero actualizado correctamente.');
    }

    public function cerrarSesion($id)
    {
        $cajero = Cajero::findOrFail($id);
        $cajero->update(['en_sesion' => false, 'sesion_inicio' => null]);

        $this->auditar('cerrar_sesion_cajero', $cajero->usuario);

        return redirect()->route('supervisor.index')
            ->with('success', 'Sesión cerrada correctamente.');
    }

    // ------------------------------------------------------------------
    // Estadísticas por operador
    // ------------------------------------------------------------------

    public function estadisticas(Request $request)
    {
        [$desde, $hasta] = $this->rangoFechas($request);
        [$porOperador, $porServicio] = $this->datosEstadisticas($desde, $hasta);

        return view('supervisor.estadisticas', compact('desde', 'hasta', 'porOperador', 'porServicio'));
    }

    public function exportarEstadisticas(Request $request)
    {
        [$desde, $hasta] = $this->rangoFechas($request);
        [$porOperador, $porServicio] = $this->datosEstadisticas($desde, $hasta);

        $this->auditar('exportar_estadisticas', "$desde a $hasta");

        $nombre = "estadisticas_{$desde}_{$hasta}.csv";

        return response()->streamDownload(function () use ($porOperador, $porServicio, $desde, $hasta) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel lea bien los acentos

            fputcsv($out, ['Periodo', $desde, $hasta]);
            fputcsv($out, []);
            fputcsv($out, ['POR OPERADOR']);
            fputcsv($out, ['Operador', 'Ventanilla', 'Atendidos', 'No se presentó', 'Tiempo promedio de atención', 'Tiempo promedio de espera']);
            foreach ($porOperador as $f) {
                fputcsv($out, [$f->nombre, $f->ventanilla, $f->atendidos, $f->no_presentados,
                    self::mmss($f->seg_atencion), self::mmss($f->seg_espera)]);
            }
            fputcsv($out, []);
            fputcsv($out, ['POR SERVICIO']);
            fputcsv($out, ['Servicio', 'Total', 'Atendidos', 'Cancelados', 'Abiertos', 'Tiempo promedio de espera']);
            foreach ($porServicio as $f) {
                fputcsv($out, [$f->servicio, $f->total, $f->atendidos, $f->cancelados, $f->abiertos, self::mmss($f->seg_espera)]);
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function rangoFechas(Request $request): array
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $desde = $request->filled('desde') ? Carbon::parse($request->desde)->toDateString() : today()->toDateString();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta)->toDateString() : $desde;

        return [$desde, $hasta];
    }

    private function datosEstadisticas(string $desde, string $hasta): array
    {
        $porOperador = DB::table('cajeros')
            ->leftJoin('turnos', function ($j) use ($desde, $hasta) {
                $j->on('turnos.cajero_id', '=', 'cajeros.id')
                    ->whereBetween('turnos.fecha', [$desde, $hasta]);
            })
            ->leftJoin('ventanillas', 'ventanillas.id', '=', 'cajeros.ventanilla_id')
            ->selectRaw("cajeros.id, cajeros.nombre, ventanillas.nombre AS ventanilla,
                COUNT(CASE WHEN turnos.estado = 'atendido' THEN 1 END) AS atendidos,
                COUNT(CASE WHEN turnos.estado = 'cancelado' THEN 1 END) AS no_presentados,
                AVG(CASE WHEN turnos.estado = 'atendido' AND turnos.llamado_at IS NOT NULL AND turnos.atendido_at IS NOT NULL
                    THEN TIMESTAMPDIFF(SECOND, turnos.llamado_at, turnos.atendido_at) END) AS seg_atencion,
                AVG(CASE WHEN turnos.llamado_at IS NOT NULL
                    THEN TIMESTAMPDIFF(SECOND, turnos.created_at, turnos.llamado_at) END) AS seg_espera")
            ->groupBy('cajeros.id', 'cajeros.nombre', 'ventanillas.nombre')
            ->orderByDesc('atendidos')
            ->orderBy('cajeros.nombre')
            ->get();

        $porServicio = DB::table('turnos')
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw("servicio,
                COUNT(*) AS total,
                COUNT(CASE WHEN estado = 'atendido' THEN 1 END) AS atendidos,
                COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) AS cancelados,
                COUNT(CASE WHEN estado IN ('pendiente', 'atendiendo') THEN 1 END) AS abiertos,
                AVG(CASE WHEN llamado_at IS NOT NULL THEN TIMESTAMPDIFF(SECOND, created_at, llamado_at) END) AS seg_espera")
            ->groupBy('servicio')
            ->orderBy('servicio')
            ->get();

        return [$porOperador, $porServicio];
    }

    public static function mmss($segundos): string
    {
        if ($segundos === null) {
            return '--';
        }
        $s = (int) round($segundos);

        return intdiv($s, 60).':'.str_pad((string) ($s % 60), 2, '0', STR_PAD_LEFT);
    }
}
