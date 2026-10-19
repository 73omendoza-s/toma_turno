<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolRequerido
{
    /** Uso en rutas: RolRequerido::class . ':admin' o ':admin,recepcion' */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if ($usuario === null || ! in_array($usuario->rol->value, $roles, true)) {
            abort(403, 'No tienes permiso para esta sección.');
        }

        return $next($request);
    }
}
