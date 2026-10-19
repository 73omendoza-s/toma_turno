<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Ventanilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class VentanillaController extends Controller
{
    private function auditar(string $accion, string $detalle): void
    {
        Bitacora::registrar('supervisor', Session::get('supervisor_id'), Session::get('supervisor_nombre'), $accion, $detalle);
    }

    public function store(Request $request)
    {
        $request->validate([
            'servicio' => ['required', Rule::exists('servicios', 'nombre')->where('activo', true)],
        ]);

        // max()+1 evita repetir número si se eliminó una ventanilla intermedia
        $numero = (int) Ventanilla::where('servicio', $request->servicio)->max('numero') + 1;
        $nombre = $request->servicio.' '.$numero;

        Ventanilla::create([
            'nombre' => $nombre,
            'servicio' => $request->servicio,
            'numero' => $numero,
            'activo' => true,
        ]);

        $this->auditar('crear_ventanilla', $nombre);

        return redirect()->route('supervisor.index')
            ->with('success', 'Ventanilla creada correctamente.');
    }

    public function destroy($id)
    {
        $ventanilla = Ventanilla::findOrFail($id);

        if ($ventanilla->cajeros()->exists() || $ventanilla->turnos()->exists()) {
            return redirect()->route('supervisor.index')->withErrors([
                'ventanilla' => 'No se puede eliminar "'.$ventanilla->nombre.'": tiene cajeros o turnos asociados. Desactívala en su lugar.',
            ]);
        }

        $nombre = $ventanilla->nombre;
        $ventanilla->delete();

        $this->auditar('eliminar_ventanilla', $nombre);

        return redirect()->route('supervisor.index')
            ->with('success', 'Ventanilla eliminada correctamente.');
    }

    public function toggleActivo($id)
    {
        $ventanilla = Ventanilla::findOrFail($id);
        $ventanilla->update(['activo' => ! $ventanilla->activo]);

        $this->auditar($ventanilla->activo ? 'activar_ventanilla' : 'desactivar_ventanilla', $ventanilla->nombre);

        return redirect()->route('supervisor.index')
            ->with('success', 'Ventanilla actualizada correctamente.');
    }
}
