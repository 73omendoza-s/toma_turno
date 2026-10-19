<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Rol;
use App\Livewire\Admin\Catalogo;
use App\Models\Area;
use App\Models\User;
use App\Models\Ventanilla;
use App\Services\TurnoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

final class CatalogoEliminarAreaTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(Rol $rol, string $usuario): User
    {
        return User::create([
            'name' => $usuario,
            'usuario' => $usuario,
            'password' => 'Secreta123',
            'rol' => $rol,
            'activo' => true,
        ]);
    }

    private function admin(): User
    {
        return $this->usuario(Rol::Admin, 'admin');
    }

    private function recepcion(): User
    {
        return $this->usuario(Rol::Recepcion, 'recepcion');
    }

    private function area(string $nombre = 'Prueba', string $prefijo = 'PRU'): Area
    {
        $area = Area::create(['nombre' => $nombre, 'prefijo' => $prefijo, 'activa' => true, 'orden' => 1]);

        // direccion_id se guarda aparte, igual que en Catalogo::guardarArea
        $direccionId = DB::table('direcciones')->insertGetId([
            'nombre' => 'Dirección ' . $prefijo,
            'activa' => true,
            'orden' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('areas')->where('id', $area->id)->update(['direccion_id' => $direccionId]);

        return $area->fresh();
    }

    public function test_elimina_area_sin_ventanillas_ni_turnos(): void
    {
        $area = $this->area();

        Livewire::actingAs($this->admin())
            ->test(Catalogo::class)
            ->call('eliminarArea', $area->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('areas', ['id' => $area->id]);
    }

    public function test_eliminar_area_deja_registro_en_bitacora(): void
    {
        $area = $this->area();
        $antes = DB::table('bitacora')->count();

        Livewire::actingAs($this->admin())
            ->test(Catalogo::class)
            ->call('eliminarArea', $area->id);

        $this->assertSame($antes + 1, DB::table('bitacora')->count());
    }

    public function test_eliminar_el_area_en_edicion_limpia_el_formulario(): void
    {
        $area = $this->area();

        Livewire::actingAs($this->admin())
            ->test(Catalogo::class)
            ->call('editarArea', $area->id)
            ->assertSet('areaId', $area->id)
            ->call('eliminarArea', $area->id)
            ->assertSet('areaId', null)
            ->assertSet('areaNombre', '');

        $this->assertDatabaseMissing('areas', ['id' => $area->id]);
    }

    public function test_bloquea_area_con_ventanillas(): void
    {
        $area = $this->area();
        Ventanilla::create(['area_id' => $area->id, 'nombre' => 'V1', 'activa' => true]);

        Livewire::actingAs($this->admin())
            ->test(Catalogo::class)
            ->call('eliminarArea', $area->id)
            ->assertHasErrors('areaNombre');

        $this->assertDatabaseHas('areas', ['id' => $area->id]);
    }

    public function test_bloquea_area_con_turnos_registrados(): void
    {
        $area = $this->area();
        (new TurnoService)->generar($area, $this->recepcion());

        Livewire::actingAs($this->admin())
            ->test(Catalogo::class)
            ->call('eliminarArea', $area->id)
            ->assertHasErrors('areaNombre');

        $this->assertDatabaseHas('areas', ['id' => $area->id]);
    }

    public function test_solo_admin_accede_al_catalogo(): void
    {
        $this->actingAs($this->recepcion())->get('/admin/catalogo')->assertForbidden();
        $this->actingAs($this->usuario(Rol::Supervisor, 'super'))->get('/admin/catalogo')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/catalogo')->assertOk();
    }
}
