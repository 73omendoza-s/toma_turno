<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso lo controla el middleware de rol en la ruta
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('prefijo')) {
            $this->merge(['prefijo' => strtoupper(trim((string) $this->input('prefijo')))]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100', 'unique:areas,nombre'],
            'prefijo' => ['required', 'alpha_num:ascii', 'max:5', 'unique:areas,prefijo'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
