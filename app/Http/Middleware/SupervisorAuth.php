<?php

namespace App\Http\Middleware;

use App\Models\Supervisor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SupervisorAuth
{
    public function handle(Request $request, Closure $next)
    {
        $supervisor = Session::has('supervisor_id') ? Supervisor::find(Session::get('supervisor_id')) : null;

        if (! $supervisor || $supervisor->activo === false) {
            Session::forget(['supervisor_id', 'supervisor_nombre']);

            return redirect()->route('supervisor.login');
        }

        return $next($request);
    }
}
