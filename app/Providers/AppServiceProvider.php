<?php

namespace App\Providers;

use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Acceso: 10 intentos por minuto para cada usuario y equipo
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(10)->by('login|'.strtolower((string) $r->input('usuario')).'|'.$r->ip()));

        // Pantalla de sala (consulta cada 3 s): su propio contador, sin afectar al acceso
        RateLimiter::for('pantalla', fn (Request $r) => Limit::perMinute(240)->by('pantalla|'.$r->ip()));

        // Al pulsar "Salir", la sesión queda libre para entrar desde otro equipo
        Event::listen(Logout::class, function (Logout $e) {
            if ($e->user && $e->user->sesion_id === session()->getId()) {
                DB::table('users')->where('id', $e->user->id)->update(['sesion_id' => null, 'ultima_actividad' => null]);
            }
        });
    }
}
