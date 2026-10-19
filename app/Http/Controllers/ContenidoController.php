<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\PantallaContenido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ContenidoController extends Controller
{
    public function store(Request $request)
    {
        // 102400 KB = 100 MB. Ajusta también upload_max_filesize y post_max_size en php.ini
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,mp4,webm', 'max:102400'],
        ]);

        $archivo = $request->file('archivo');
        $tipo = str_contains((string) $archivo->getMimeType(), 'video') ? 'video' : 'imagen';
        $nombreLimpio = 'contenido_'.time().'.'.$archivo->getClientOriginalExtension();
        $ruta = $archivo->storeAs('pantalla', $nombreLimpio, 'public');

        PantallaContenido::create([
            'tipo' => $tipo,
            'archivo' => $ruta,
            'activo' => true,
        ]);

        Bitacora::registrar('supervisor', Session::get('supervisor_id'), Session::get('supervisor_nombre'),
            'subir_contenido', $nombreLimpio);

        return redirect()->route('supervisor.index')
            ->with('success', 'Contenido subido correctamente.');
    }

    public function destroy($id)
    {
        $contenido = PantallaContenido::findOrFail($id);

        if ($contenido->archivo) {
            Storage::disk('public')->delete($contenido->archivo);
        }

        $nombre = basename((string) $contenido->archivo);
        $contenido->delete();

        Bitacora::registrar('supervisor', Session::get('supervisor_id'), Session::get('supervisor_nombre'),
            'eliminar_contenido', $nombre);

        return redirect()->route('supervisor.index')
            ->with('success', 'Contenido eliminado correctamente.');
    }

    public function toggle($id)
    {
        $contenido = PantallaContenido::findOrFail($id);
        $contenido->update(['activo' => ! $contenido->activo]);

        return redirect()->route('supervisor.index')
            ->with('success', 'Contenido actualizado correctamente.');
    }
}
