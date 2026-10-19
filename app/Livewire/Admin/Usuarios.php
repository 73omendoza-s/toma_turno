<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\Rol;
use App\Models\Configuracion;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Usuarios')]
class Usuarios extends Component
{
    use RegistraBitacora;

    public ?int $userId = null;

    public string $name = '';

    public string $usuario = '';

    public string $password = '';

    public string $rol = 'operador';

    public ?int $ventanilla_id = null;

    public ?int $direccion_id = null;

    public function editar(int $id): void
    {
        $u = User::findOrFail($id);
        $this->userId = $u->id;
        $this->name = $u->name;
        $this->usuario = $u->usuario;
        $this->rol = $u->rol->value;
        $this->ventanilla_id = $u->ventanilla_id;
        $this->direccion_id = $u->direccion_id ? (int) $u->direccion_id : null;
        $this->password = '';
    }

    public function cancelar(): void
    {
        $this->reset('userId', 'name', 'usuario', 'password', 'ventanilla_id', 'direccion_id');
        $this->rol = 'operador';
    }

    public function guardar(): void
    {
        $d = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'usuario' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'usuario')->ignore($this->userId)],
            'password' => [$this->userId ? 'nullable' : 'required', Password::min(8)->mixedCase()->numbers()],
            'rol' => ['required', Rule::enum(Rol::class)],
            'ventanilla_id' => [
                Rule::requiredIf($this->rol === Rol::Operador->value),
                'nullable',
                Rule::exists('ventanillas', 'id')->where('activa', true),
            ],
            // El supervisor ve las estadísticas de su dirección
            'direccion_id' => [
                Rule::requiredIf($this->rol === 'supervisor'),
                'nullable',
                Rule::exists('direcciones', 'id')->where('activa', true),
            ],
        ], [
            'direccion_id.required' => 'Elige la dirección de este supervisor.',
        ]);

        $datos = [
            'name' => $d['name'],
            'usuario' => $d['usuario'],
            'rol' => $d['rol'],
            // Solo los operadores tienen ventanilla.
            'ventanilla_id' => $d['rol'] === Rol::Operador->value ? $d['ventanilla_id'] : null,
        ];

        if (($d['password'] ?? '') !== '') {
            $datos['password'] = Hash::make($d['password']);
        }

        $u = $this->userId
            ? tap(User::findOrFail($this->userId))->update($datos)
            : User::create($datos + ['activo' => true]);

        // Se guarda aparte para no depender de los campos permitidos del modelo
        DB::table('users')->where('id', $u->id)->update([
            'direccion_id' => $d['rol'] === 'supervisor' ? $d['direccion_id'] : null,
        ]);

        $this->registrar($this->userId ? 'usuario_editado' : 'usuario_creado', "Usuario {$u->usuario}");
        $this->cancelar();
    }

    public function toggle(int $id): void
    {
        $u = User::findOrFail($id);

        if ($u->id === auth()->id()) {
            $this->addError('usuario', 'No puedes desactivarte a ti mismo.');

            return;
        }

        $u->update(['activo' => ! $u->activo]);

        // Un usuario dado de baja no debe conservar su sesión abierta
        if (! $u->activo) {
            $this->cerrarSesion($u);
        }

        $this->registrar($u->activo ? 'usuario_activado' : 'usuario_desactivado', "Usuario {$u->usuario}");
    }

    /** Libera la sesión de un usuario (por ejemplo, si cerró el navegador sin pulsar Salir). */
    public function liberarSesion(int $id): void
    {
        $u = User::findOrFail($id);

        if ($u->id === auth()->id()) {
            $this->addError('usuario', 'No puedes liberar tu propia sesión; usa el botón Salir.');

            return;
        }

        $this->cerrarSesion($u);
        $this->registrar('sesion_liberada', "Usuario {$u->usuario}");
    }

    /** Cierra de verdad la sesión anterior (si existe) y deja al usuario libre para entrar de nuevo. */
    private function cerrarSesion(User $u): void
    {
        if ($u->sesion_id) {
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('id', $u->sesion_id)->delete();
            } elseif (config('session.driver') === 'file') {
                @unlink(config('session.files', storage_path('framework/sessions')).DIRECTORY_SEPARATOR.$u->sesion_id);
            }
        }

        DB::table('users')->where('id', $u->id)->update(['sesion_id' => null, 'ultima_actividad' => null]);
    }

    public function render()
    {
        return view('livewire.admin.usuarios', [
            'usuarios' => User::with('ventanilla.area')->orderBy('name')->get(),
            'ventanillas' => Ventanilla::with('area')->where('activa', true)->get()
                ->sortBy(fn ($v) => $v->area->nombre.$v->nombre),
            'roles' => Rol::cases(),
            'direcciones' => DB::table('direcciones')->where('activa', true)->orderBy('orden')->get(),
            'nombresDireccion' => DB::table('direcciones')->pluck('nombre', 'id'),
            'minutosSesion' => Configuracion::entero('sesion_inactividad_min', 5),
        ]);
    }
}
