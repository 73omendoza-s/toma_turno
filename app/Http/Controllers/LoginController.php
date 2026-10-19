<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Configuracion;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function formulario(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect(Auth::user()->rol->inicio());
        }

        return view('auth.login');
    }

    public function entrar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'usuario' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        // 'activo' => true impide entrar a un usuario dado de baja.
        if (! Auth::attempt([...$datos, 'activo' => true], $request->boolean('recordar'))) {
            Bitacora::registrar('desconocido', null, $datos['usuario'], 'LOGIN_FALLIDO');

            return back()->withErrors(['usuario' => 'Usuario o contraseña incorrectos.'])->onlyInput('usuario');
        }

        $u = Auth::user();

        // ¿Este usuario ya tiene una sesión abierta y activa en otro navegador o equipo?
        $minutos = Configuracion::entero('sesion_inactividad_min', 5);
        $ultima = $u->ultima_actividad ? Carbon::parse($u->ultima_actividad) : null;

        if ($u->sesion_id && $ultima && $ultima->gt(now()->subMinutes($minutos))) {
            Bitacora::registrar($u->rol->value, $u->id, $u->name, 'LOGIN_SESION_ABIERTA');

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['usuario' => 'Este usuario ya tiene una sesión abierta en otro equipo o navegador. '
                    .'Ciérrala con el botón Salir o espera unos minutos sin actividad; '
                    .'si no puedes, pide al administrador que la libere.'])
                ->withInput($request->only('usuario'));
        }

        $request->session()->regenerate();

        // Esta pasa a ser la sesión vigente del usuario
        DB::table('users')->where('id', $u->id)->update([
            'sesion_id' => $request->session()->getId(),
            'ultima_actividad' => now(),
        ]);

        Bitacora::registrar($u->rol->value, $u->id, $u->name, 'LOGIN');

        return redirect($u->rol->inicio());
    }

    public function salir(Request $request): RedirectResponse
    {
        $u = Auth::user();
        if ($u) {
            Bitacora::registrar($u->rol->value, $u->id, $u->name, 'LOGOUT');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
