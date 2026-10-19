<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VentanillaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_id' => ['required', 'exists:areas,id'],
            'nombre' => [
                'required', 'string', 'max:60',
                Rule::unique('ventanillas', 'nombre')->where('area_id', $this->input('area_id')),
            ],
        ];
    }

    public function messages(): array
    {
        return ['nombre.unique' => 'Esa área ya tiene una ventanilla con ese nombre.'];
    }
}
