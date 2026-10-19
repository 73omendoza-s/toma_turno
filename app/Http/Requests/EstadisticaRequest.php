<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstadisticaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['nullable', 'date_format:Y-m-d'],
            'fecha_fin' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function inicio(): string
    {
        return $this->validated('fecha_inicio') ?? now()->toDateString();
    }

    public function fin(): string
    {
        return $this->validated('fecha_fin') ?? $this->inicio();
    }
}
