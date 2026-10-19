<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\Area;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_seeder_crea_catalogo_y_usuarios_iniciales(): void
    {
        $this->seed();

        $this->assertSame(8, Area::count());
        $this->assertSame(16, Ventanilla::count());
        $this->assertSame(3, User::count());

        $caja1 = User::where('usuario', 'caja1')->firstOrFail();
        $this->assertSame(Rol::Operador, $caja1->rol);
        $this->assertNotNull($caja1->ventanilla_id);
        $this->assertSame('Caja 1', Ventanilla::findOrFail($caja1->ventanilla_id)->nombre);
    }

    public function test_el_admin_sembrado_puede_iniciar_sesion(): void
    {
        $this->seed();

        $this->post('/login', [
            'usuario' => 'admin',
            'password' => 'Admin2026!',
        ])->assertRedirect();

        $this->assertAuthenticated();
        $this->assertSame(Rol::Admin, auth()->user()->rol);
    }

    public function test_el_seeder_es_idempotente(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(8, Area::count());
        $this->assertSame(16, Ventanilla::count());
        $this->assertSame(3, User::count());
    }
}
