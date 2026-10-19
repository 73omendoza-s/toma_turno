<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with('ventanilla.area')->orderBy('rol')->orderBy('name')->get();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'ventanillas' => $this->ventanillasActivas(),
            'roles' => Rol::cases(),
        ]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        if ($datos['rol'] !== Rol::Ventanilla->value) {
            $datos['ventanilla_id'] = null;
        }

        User::create($datos);

        return back()->with('ok', 'Usuario dado de alta.');
    }

    public function edit(User $usuario): View
    {
        return view('admin.usuarios.edit', [
            'usuario' => $usuario,
            'ventanillas' => $this->ventanillasActivas(),
            'roles' => Rol::cases(),
        ]);
    }

    public function update(UsuarioRequest $request, User $usuario): RedirectResponse
    {
        $datos = $request->validated();

        if ($usuario->is($request->user()) && (! $datos['activo'] || $datos['rol'] !== Rol::Admin->value)) {
            return back()->with('error', 'No puedes desactivarte ni quitarte el rol de administrador a ti mismo.');
        }

        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        if ($datos['rol'] !== Rol::Ventanilla->value) {
            $datos['ventanilla_id'] = null;
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    private function ventanillasActivas()
    {
        return Ventanilla::with('area')->where('activa', true)->orderBy('area_id')->orderBy('id')->get();
    }
}
