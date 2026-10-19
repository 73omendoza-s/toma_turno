<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Configuracion as Config;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Parámetros generales. Hoy: tiempo máximo de atención antes de avisar al operador. */
#[Layout('components.layouts.app')]
#[Title('Configuración')]
class Configuracion extends Component
{
    use RegistraBitacora;

    public string $tiempoAtencion = '15';

    public string $tiempoExtension = '5';

    public string $sesionInactividad = '5';

    public bool $guardado = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->rol->value === 'admin', 403);
        $this->tiempoAtencion = (string) Config::entero('tiempo_atencion_min', 15);
        $this->tiempoExtension = (string) Config::entero('tiempo_extension_min', 5);
        $this->sesionInactividad = (string) Config::entero('sesion_inactividad_min', 5);
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()->rol->value === 'admin', 403);

        $d = $this->validate([
            'tiempoAtencion' => ['required', 'integer', 'between:5,120'],
            'tiempoExtension' => ['required', 'integer', 'between:1,30'],
            'sesionInactividad' => ['required', 'integer', 'between:2,240'],
        ], [], [
            'tiempoAtencion' => 'tiempo de atención',
            'tiempoExtension' => 'tiempo de extensión',
            'sesionInactividad' => 'tiempo de inactividad de la sesión',
        ]);

        $antesA = Config::entero('tiempo_atencion_min', 15);
        $antesE = Config::entero('tiempo_extension_min', 5);
        $antesS = Config::entero('sesion_inactividad_min', 5);

        Config::guardar('tiempo_atencion_min', (int) $d['tiempoAtencion']);
        Config::guardar('tiempo_extension_min', (int) $d['tiempoExtension']);
        Config::guardar('sesion_inactividad_min', (int) $d['sesionInactividad']);

        $this->registrar(
            'configuracion_actualizada',
            "Aviso de atención: {$antesA} → {$d['tiempoAtencion']} min; extensión: {$antesE} → {$d['tiempoExtension']} min; sesión inactiva: {$antesS} → {$d['sesionInactividad']} min"
        );

        $this->guardado = true;
    }

    public function updated(): void
    {
        $this->guardado = false;
    }

    public function render()
    {
        return view('livewire.admin.configuracion');
    }
}
