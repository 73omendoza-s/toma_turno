<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Rol;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRol
{
    /** Uso: ->middleware('rol:admin,supervisor') */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $permitidos = array_filter(array_map(fn ($r) => Rol::tryFrom($r), $roles));

        if ($permitidos === []) {
            throw new \LogicException('Middleware rol: ningún rol válido en '.implode(',', $roles));
        }

        abort_unless($request->user()?->tieneRol(...$permitidos), 403, 'No tienes permiso para esta sección.');

        return $next($request);
    }
}
