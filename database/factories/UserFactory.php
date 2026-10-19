<?php

namespace Database\Factories;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'usuario' => $this->faker->unique()->userName(),
            'clave_empleado' => $this->faker->unique()->numerify('EMP###'),
            'password' => Hash::make('Secreta123'),
            'rol' => Rol::Operador,   // 👈 Ajusta según el caso que quieras probar
            'activo' => true,
            'ventanilla_id' => null,  // o asigna una ventanilla si aplica
        ];
    }

    // Ejemplo de estados para roles específicos
    public function admin(): static
    {
        return $this->state(fn () => ['rol' => Rol::Admin]);
    }

    public function recepcion(): static
    {
        return $this->state(fn () => ['rol' => Rol::Recepcion]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            // Si no usas email_verified_at, puedes marcarlo como inactivo
            'activo' => false,
        ]);
    }
}
