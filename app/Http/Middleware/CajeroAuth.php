<?php

namespace App\Http\Middleware;

use App\Models\Cajero;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CajeroAuth
{
    public function handle(Request $request, Closure $next)
    {
        $cajero = Session::has('cajero_id') ? Cajero::find(Session::get('cajero_id')) : null;

        // La sesión debe seguir vigente en la base de datos: si el supervisor la cerró,
        // desactivó al cajero, o alguien más inició sesión con la misma cuenta, se expulsa.
        $valida = $cajero
            && $cajero->activo
            && $cajero->en_sesion
            && $cajero->sesion_inicio?->timestamp === Session::get('cajero_sesion');

        if (! $valida) {
            Session::forget(['cajero_id', 'cajero_nombre', 'cajero_caja', 'cajero_sesion']);

            return redirect()->route('cajero.login')
                ->withErrors(['usuario' => 'Debes iniciar sesión.']);
        }

        // Marca de actividad (máximo una escritura por minuto)
        if (! $cajero->ultima_actividad || $cajero->ultima_actividad->lt(now()->subMinute())) {
            Cajero::where('id', $cajero->id)->update(['ultima_actividad' => now()]);
        }

        // La caja de la URL debe ser la del cajero en sesión
        $cajaEnUrl = $request->route('caja');
        if ($cajaEnUrl && $cajaEnUrl != $cajero->numero_caja) {
            return redirect()->route('cajero.index', $cajero->numero_caja);
        }

        return $next($request);
    }
}
