<?php

namespace App\Http\Controllers;

use App\Models\Cajero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class CajeroAuthController extends Controller
{
    public function showLogin()
    {
        return view('cajero.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        $cajero = Cajero::where('usuario', $request->usuario)
            ->where('activo', true)
            ->first();

        if (! $cajero || ! Hash::check($request->password, $cajero->password)) {
            return back()->withErrors([
                'usuario' => 'Usuario o contraseña incorrectos.',
            ])->onlyInput('usuario');
        }

        // Sesión activa = marcada en BD y con actividad reciente.
        // Si el equipo se apagó o cerraron el navegador, se libera sola al vencer la inactividad.
        $limite = (int) config('session.lifetime', 120);
        $vigente = $cajero->en_sesion
            && $cajero->ultima_actividad
            && $cajero->ultima_actividad->gt(now()->subMinutes($limite));

        if ($vigente) {
            return back()->withErrors([
                'usuario' => 'Este usuario ya tiene una sesión activa en '.$cajero->numero_caja.'.',
            ])->onlyInput('usuario');
        }

        $request->session()->regenerate();

        $ahora = now();
        $cajero->update([
            'en_sesion' => true,
            'sesion_inicio' => $ahora,
            'ultima_actividad' => $ahora,
        ]);
        $cajero->refresh();

        Session::put('cajero_id', $cajero->id);
        Session::put('cajero_nombre', $cajero->nombre);
        Session::put('cajero_caja', $cajero->numero_caja);
        Session::put('cajero_sesion', $cajero->sesion_inicio->timestamp);

        return redirect()->route('cajero.index', $cajero->numero_caja);
    }

    public function logout(Request $request)
    {
        $cajero = Cajero::find(Session::get('cajero_id'));

        // Solo libera la sesión si es la que inició esta misma sesión de navegador
        if ($cajero && $cajero->sesion_inicio?->timestamp === Session::get('cajero_sesion')) {
            $cajero->update(['en_sesion' => false, 'sesion_inicio' => null]);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cajero.login');
    }
}
