<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EstadoTurno;
use App\Enums\Rol;
use App\Models\Area;
use App\Models\User;
use App\Models\Ventanilla;
use App\Services\TurnoService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TurnosTest extends TestCase
{
    use RefreshDatabase;

    private function area(string $nombre, string $prefijo): Area
    {
        return Area::create(['nombre' => $nombre, 'prefijo' => $prefijo, 'activa' => true, 'orden' => 1]);
    }

    private function operador(Area $area, string $usuario): User
    {
        $ventanilla = Ventanilla::create(['area_id' => $area->id, 'nombre' => "V {$usuario}", 'activa' => true]);

        return User::create([
            'name' => $usuario,
            'usuario' => $usuario,
            'password' => 'Secreta123',
            'rol' => Rol::Operador,
            'activo' => true,
            'ventanilla_id' => $ventanilla->id,
        ]);
    }

    private function recepcion(): User
    {
        return User::create([
            'name' => 'Recepción',
            'usuario' => 'recepcion',
            'password' => 'Secreta123',
            'rol' => Rol::Recepcion,
            'activo' => true,
        ]);
    }

    public function test_los_folios_son_consecutivos_por_area(): void
    {
        $cajas = $this->area('Cajas', 'CAJ');
        $sare = $this->area('SARE', 'SARE');
        $recepcion = $this->recepcion();
        $servicio = new TurnoService;

        $this->assertSame('CAJ-001', $servicio->generar($cajas, $recepcion)->folio);
        $this->assertSame('CAJ-002', $servicio->generar($cajas, $recepcion)->folio);
        $this->assertSame('SARE-001', $servicio->generar($sare, $recepcion)->folio);
    }

    public function test_dos_ventanillas_del_mismo_area_no_reciben_el_mismo_turno(): void
    {
        $cajas = $this->area('Cajas', 'CAJ');
        $recepcion = $this->recepcion();
        $a = $this->operador($cajas, 'cajaa');
        $b = $this->operador($cajas, 'cajab');
        $servicio = new TurnoService;

        $servicio->generar($cajas, $recepcion);
        $servicio->generar($cajas, $recepcion);

        $this->assertSame('CAJ-001', $servicio->llamarSiguiente($a)->folio);
        $this->assertSame('CAJ-002', $servicio->llamarSiguiente($b)->folio);
        $this->assertNull($servicio->llamarSiguiente($this->operador($cajas, 'cajac')));
    }

    public function test_no_se_puede_llamar_otro_turno_sin_finalizar_el_actual(): void
    {
        $cajas = $this->area('Cajas', 'CAJ');
        $recepcion = $this->recepcion();
        $operador = $this->operador($cajas, 'cajaa');
        $servicio = new TurnoService;

        $servicio->generar($cajas, $recepcion);
        $servicio->generar($cajas, $recepcion);
        $servicio->llamarSiguiente($operador);

        $this->expectException(DomainException::class);
        $servicio->llamarSiguiente($operador);
    }

    public function test_finalizar_registra_estado_y_tiempos(): void
    {
        $cajas = $this->area('Cajas', 'CAJ');
        $operador = $this->operador($cajas, 'cajaa');
        $servicio = new TurnoService;

        $servicio->generar($cajas, $this->recepcion());
        $turno = $servicio->llamarSiguiente($operador);

        $servicio->finalizar($turno, $operador);

        $turno->refresh();
        $this->assertSame(EstadoTurno::Finalizado, $turno->estado);
        $this->assertNotNull($turno->finalizado_en);
        $this->assertNotNull($turno->atencion_seg);
        $this->assertNotNull($turno->espera_seg);
    }

    public function test_un_operador_no_puede_cerrar_el_turno_de_otro(): void
    {
        $cajas = $this->area('Cajas', 'CAJ');
        $a = $this->operador($cajas, 'cajaa');
        $b = $this->operador($cajas, 'cajab');
        $servicio = new TurnoService;

        $servicio->generar($cajas, $this->recepcion());
        $turno = $servicio->llamarSiguiente($a);

        $this->expectException(DomainException::class);
        $servicio->finalizar($turno, $b);
    }

    public function test_el_login_redirige_segun_el_rol(): void
    {
        $operador = $this->operador($this->area('Cajas', 'CAJ'), 'cajaa');

        $this->post('/login', [
            'usuario' => $operador->usuario,
            'password' => 'Secreta123',
        ])->assertRedirect('/operador');
    }

    public function test_un_operador_no_entra_a_administracion(): void
    {
        $operador = $this->operador($this->area('Cajas', 'CAJ'), 'cajaa');

        $this->actingAs($operador)->get('/admin/estadisticas')->assertForbidden();
        $this->actingAs($operador)->get('/recepcion')->assertForbidden();
    }

    public function test_las_pantallas_principales_cargan(): void
    {
        $cajas = $this->area('Cajas', 'CAJ');
        $operador = $this->operador($cajas, 'cajaa');
        $recepcion = $this->recepcion();
        $admin = User::create([
            'name' => 'Admin',
            'usuario' => 'admin',
            'password' => 'Secreta123',
            'rol' => Rol::Admin,
            'activo' => true,
        ]);

        $turno = (new TurnoService)->generar($cajas, $recepcion);

        $this->actingAs($recepcion)->get('/recepcion')->assertOk();
        $this->actingAs($recepcion)->get(route('ticket.imprimir', $turno))->assertOk()->assertSee('CAJ-001');
        $this->actingAs($operador)->get('/operador')->assertOk();
        $this->actingAs($admin)->get('/admin/estadisticas')->assertOk();
        $this->actingAs($admin)->get(route('admin.usuarios'))->assertOk();
        $this->get(route('pantalla'))->assertOk();

        // TelevisorController@datos devuelve 'actual' y 'ultimos'
        $this->getJson(route('pantalla.datos'))
            ->assertOk()
            ->assertJsonStructure(['actual', 'ultimos']);
    }

    public function test_pantalla_sin_turnos_llamados_devuelve_estructura_vacia(): void
    {
        $cajas = $this->area('Cajas', 'CAJ');

        // Hay un turno generado pero aún no llamado: no debe aparecer en pantalla.
        (new TurnoService)->generar($cajas, $this->recepcion());

        $this->getJson(route('pantalla.datos'))
            ->assertOk()
            ->assertExactJson([
                'actual' => null,
                'ultimos' => [],
            ]);
    }

    public function test_pantalla_publica_solo_expone_campos_permitidos(): void
    {
        $cajas = $this->area('Cajas', 'CAJ');
        $recepcion = $this->recepcion();
        $operador = $this->operador($cajas, 'cajaa');
        $servicio = new TurnoService;

        $servicio->generar($cajas, $recepcion);
        $servicio->generar($cajas, $recepcion);
        $servicio->llamarSiguiente($operador);

        $respuesta = $this->getJson(route('pantalla.datos'))
            ->assertOk()
            ->assertJsonPath('actual.folio', 'CAJ-001')
            ->assertJsonPath('actual.area', 'Cajas');

        // Contrato exacto: solo estas claves, sin datos personales.
        $claves = array_keys($respuesta->json('actual'));
        sort($claves);
        $this->assertSame(['area', 'estado', 'folio', 'token', 'ventanilla'], $claves);

        // Con un solo turno llamado, 'ultimos' queda vacío.
        $this->assertSame([], $respuesta->json('ultimos'));
    }
}
