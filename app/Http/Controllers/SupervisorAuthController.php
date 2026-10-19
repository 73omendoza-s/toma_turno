<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SupervisorAuthController extends Controller
{
    public function showLogin()
    {
        return view('supervisor.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario' => ['required'],
            'password' => ['required'],
        ]);

        $supervisor = Supervisor::where('usuario', $request->usuario)
            ->where('activo', true)
            ->first();

        if (! $supervisor || ! Hash::check($request->password, $supervisor->password)) {
            return back()->withErrors([
                'usuario' => 'Usuario o contraseña incorrectos.',
            ])->onlyInput('usuario');
        }

        $request->session()->regenerate();

        Session::put('supervisor_id', $supervisor->id);
        Session::put('supervisor_nombre', $supervisor->nombre);

        Bitacora::registrar('supervisor', $supervisor->id, $supervisor->nombre, 'login');

        return redirect()->route('supervisor.index');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('supervisor.login');
    }
}
