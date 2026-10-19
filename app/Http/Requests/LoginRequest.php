<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $llave = Str::lower((string) $this->input('usuario')).'|'.$this->ip();

        if (RateLimiter::tooManyAttempts($llave, 5)) {
            throw ValidationException::withMessages([
                'usuario' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($llave).' segundos.',
            ]);
        }

        $credenciales = [
            'usuario' => $this->input('usuario'),
            'password' => $this->input('password'),
            'activo' => true,
        ];

        if (! Auth::attempt($credenciales)) {
            RateLimiter::hit($llave);

            throw ValidationException::withMessages(['usuario' => 'Usuario o contraseña incorrectos.']);
        }

        RateLimiter::clear($llave);
    }
}
