<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Configuracion;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un usuario solo puede tener una sesión abierta a la vez.
 * Si entra desde otro navegador o equipo mientras la primera sigue activa, se le avisa y no se le deja pasar.
 * Una sesión se considera abandonada tras N minutos sin actividad (configuración 'sesion_inactividad_min').
 */
class SesionUnica
{
    public function handle(Request $request, Closure $next): Response
    {
        $u = $request->user();
        if (! $u || ! $request->hasSession()) {
            return $next($request);
        }

        $id = $request->session()->getId();
        $ultima = $u->ultima_actividad ? Carbon::parse($u->ultima_actividad) : null;

        // Es la sesión vigente del usuario: se marca que sigue activa (como máximo una vez por minuto)
        if ($u->sesion_id === $id) {
            if (! $ultima || $ultima->lt(now()->subSeconds(60))) {
                DB::table('users')->where('id', $u->id)->update(['ultima_actividad' => now()]);
            }

            return $next($request);
        }

        // Hay otra sesión del mismo usuario y sigue activa: esta se cierra y se avisa
        $minutos = Configuracion::entero('sesion_inactividad_min', 5);
        if ($u->sesion_id && $ultima && $ultima->gt(now()->subMinutes($minutos))) {
            $usuario = $u->usuario;
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['mensaje' => 'Se abrió tu usuario en otro equipo o navegador.'], 409);
            }

            return response()->view('sesion.ocupada', ['usuario' => $usuario], 409);
        }

        // Nadie más lo está usando: esta pasa a ser la sesión vigente
        DB::table('users')->where('id', $u->id)->update(['sesion_id' => $id, 'ultima_actividad' => now()]);

        return $next($request);
    }
}
