<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Bitacora;
use App\Models\Cajero;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario' => ['required'],
            'password' => ['required'],
        ]);

        $admin = Administrador::where('usuario', $request->usuario)
            ->where('activo', true)
            ->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            return back()->withErrors([
                'usuario' => 'Usuario o contraseña incorrectos.',
            ])->onlyInput('usuario');
        }

        $request->session()->regenerate();

        Session::put('admin_id', $admin->id);
        Session::put('admin_nombre', $admin->nombre);

        Bitacora::registrar('admin', $admin->id, $admin->nombre, 'login');

        return redirect()->route('admin.index');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function index()
    {
        $supervisores = Supervisor::orderBy('nombre')->get();
        $cajeros = Cajero::with('ventanilla')->orderBy('nombre')->get();
        $bitacora = Bitacora::orderByDesc('id')->take(30)->get();

        return view('admin.index', compact('supervisores', 'cajeros', 'bitacora'));
    }

    public function resetSupervisor(Request $request, $id)
    {
        $request->validate(['password' => ['required', 'min:6']]);

        $supervisor = Supervisor::findOrFail($id);
        $supervisor->update(['password' => Hash::make($request->password)]);

        Bitacora::registrar('admin', Session::get('admin_id'), Session::get('admin_nombre'),
            'reset_password_supervisor', 'Supervisor: '.$supervisor->usuario);

        return redirect()->route('admin.index')
            ->with('success', 'Contraseña del supervisor actualizada.');
    }

    public function resetCajero(Request $request, $id)
    {
        $request->validate(['password' => ['required', 'min:6']]);

        $cajero = Cajero::findOrFail($id);
        $cajero->update(['password' => Hash::make($request->password)]);

        Bitacora::registrar('admin', Session::get('admin_id'), Session::get('admin_nombre'),
            'reset_password_cajero', 'Cajero: '.$cajero->usuario);

        return redirect()->route('admin.index')
            ->with('success', 'Contraseña del cajero actualizada.');
    }
}
