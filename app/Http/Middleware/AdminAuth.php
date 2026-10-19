<?php

namespace App\Http\Middleware;

use App\Models\Administrador;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $admin = Session::has('admin_id') ? Administrador::find(Session::get('admin_id')) : null;

        if (! $admin || $admin->activo === false) {
            Session::forget(['admin_id', 'admin_nombre']);

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
