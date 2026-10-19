<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Un checkbox sin marcar no se envía: se interpreta como false.
        $this->merge(['activo' => $this->boolean('activo')]);
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario'); // null al crear
        $creando = $usuario === null;

        return [
            'name' => ['required', 'string', 'max:120'],
            'usuario' => [
                'required', 'string', 'max:50', 'alpha_dash:ascii',
                Rule::unique('users', 'usuario')->ignore($usuario?->id),
            ],
            'password' => [$creando ? 'required' : 'nullable', Password::min(8)],
            'rol' => ['required', Rule::enum(Rol::class)],
            'ventanilla_id' => [
                Rule::requiredIf($this->input('rol') === Rol::Ventanilla->value),
                'nullable', 'exists:ventanillas,id',
            ],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return ['ventanilla_id.required' => 'El operador de ventanilla debe tener una ventanilla asignada.'];
    }
}
