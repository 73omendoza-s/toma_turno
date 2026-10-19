<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\EstadoTurno;
use App\Models\Area;
use App\Models\Turno;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Direcciones, áreas y ventanillas. Cada área pertenece a una dirección y cada ventanilla a un área.
 * Las direcciones nunca se borran (se desactivan). Un área solo se elimina si no tiene ventanillas ni turnos.
 * Una ventanilla solo se elimina si nunca tuvo turnos ni operadores.
 */
#[Layout('components.layouts.app')]
#[Title('Direcciones, áreas y ventanillas')]
class Catalogo extends Component
{
    use RegistraBitacora;

    public ?int $dirId = null;

    public string $dirNombre = '';

    public ?int $areaId = null;

    public string $areaNombre = '';

    public string $areaPrefijo = '';

    public ?int $areaDireccionId = null;

    public ?int $ventId = null;

    public ?int $ventAreaId = null;

    public string $ventNombre = '';

    // ---------------------------------------------------------------- Direcciones

    public function editarDireccion(int $id): void
    {
        $d = DB::table('direcciones')->where('id', $id)->first();
        abort_unless($d, 404);
        $this->resetErrorBag();
        $this->dirId = $d->id;
        $this->dirNombre = $d->nombre;
    }

    public function cancelarDireccion(): void
    {
        $this->resetErrorBag();
        $this->reset('dirId', 'dirNombre');
    }

    public function guardarDireccion(): void
    {
        $d = $this->validate([
            'dirNombre' => ['required', 'string', 'max:100', Rule::unique('direcciones', 'nombre')->ignore($this->dirId)],
        ]);

        if ($this->dirId) {
            DB::table('direcciones')->where('id', $this->dirId)->update(['nombre' => $d['dirNombre'], 'updated_at' => now()]);
            $this->registrar('direccion_editada', "Dirección {$d['dirNombre']}");
        } else {
            DB::table('direcciones')->insert([
                'nombre' => $d['dirNombre'],
                'activa' => true,
                'orden' => (int) DB::table('direcciones')->max('orden') + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->registrar('direccion_creada', "Dirección {$d['dirNombre']}");
        }

        $this->cancelarDireccion();
    }

    public function toggleDireccion(int $id): void
    {
        $d = DB::table('direcciones')->where('id', $id)->first();
        abort_unless($d, 404);

        if ($d->activa && Area::where('direccion_id', $id)->where('activa', true)->exists()) {
            $this->addError('dirNombre', 'La dirección tiene áreas activas. Desactívalas o pásalas a otra dirección primero.');

            return;
        }

        DB::table('direcciones')->where('id', $id)->update(['activa' => ! $d->activa, 'updated_at' => now()]);
        $this->registrar($d->activa ? 'direccion_desactivada' : 'direccion_activada', "Dirección {$d->nombre}");
    }

    // ---------------------------------------------------------------- Áreas

    public function editarArea(int $id): void
    {
        $a = Area::findOrFail($id);
        $this->resetErrorBag();
        $this->areaId = $a->id;
        $this->areaNombre = $a->nombre;
        $this->areaPrefijo = (string) $a->prefijo;
        $this->areaDireccionId = $a->direccion_id ? (int) $a->direccion_id : null;
    }

    public function cancelarArea(): void
    {
        $this->resetErrorBag();
        $this->reset('areaId', 'areaNombre', 'areaPrefijo', 'areaDireccionId');
    }

    public function guardarArea(): void
    {
        $d = $this->validate([
            'areaNombre' => ['required', 'string', 'max:100', Rule::unique('areas', 'nombre')->ignore($this->areaId)],
            'areaPrefijo' => ['required', 'alpha_num', 'max:5', Rule::unique('areas', 'prefijo')->ignore($this->areaId)],
            'areaDireccionId' => ['required', Rule::exists('direcciones', 'id')->where('activa', true)],
        ], [
            'areaDireccionId.required' => 'Elige la dirección a la que pertenece el área.',
            'areaDireccionId.exists' => 'La dirección elegida no existe o está inactiva.',
        ]);

        $datos = ['nombre' => $d['areaNombre'], 'prefijo' => strtoupper($d['areaPrefijo'])];

        $area = $this->areaId
            ? tap(Area::findOrFail($this->areaId))->update($datos)
            : Area::create($datos + ['orden' => (int) Area::max('orden') + 1]);

        // Se guarda aparte para no depender de los campos permitidos del modelo
        DB::table('areas')->where('id', $area->id)->update(['direccion_id' => $d['areaDireccionId']]);

        $this->registrar($this->areaId ? 'area_editada' : 'area_creada', "Área {$area->nombre} (dirección #{$d['areaDireccionId']})");
        $this->cancelarArea();
    }

    public function toggleArea(int $id): void
    {
        $a = Area::findOrFail($id);

        if ($a->activa) {
            $pendientes = Turno::where('area_id', $a->id)
                ->where('fecha', now()->toDateString())
                ->whereIn('estado', [EstadoTurno::Espera->value, EstadoTurno::Atendiendo->value])
                ->exists();

            if ($pendientes) {
                $this->addError('areaNombre', 'El área tiene turnos en espera o en atención hoy.');

                return;
            }
        }

        $a->update(['activa' => ! $a->activa]);
        $this->registrar($a->activa ? 'area_activada' : 'area_desactivada', "Área {$a->nombre}");
    }
    /** Solo elimina un área sin ventanillas y sin turnos registrados. */
    public function eliminarArea(int $id): void
    {
        $a = Area::findOrFail($id);

        if ($a->ventanillas()->exists()) {
            $this->addError('areaNombre', "El área {$a->nombre} tiene ventanillas. Elimínalas primero.");

            return;
        }

        // Protege el historial y las estadísticas: los turnos emitidos deben conservar su área.
        if (Turno::where('area_id', $a->id)->exists()) {
            $this->addError('areaNombre', "El área {$a->nombre} tiene turnos registrados y no puede eliminarse. Desactívala.");

            return;
        }

        $detalle = "Área {$a->nombre} ({$a->prefijo})";
        $a->delete();

        if ($this->areaId === $id) {
            $this->cancelarArea();
        }

        $this->registrar('area_eliminada', $detalle);
    }
    // ---------------------------------------------------------------- Ventanillas
    public function editarVentanilla(int $id): void
    {
        $v = Ventanilla::findOrFail($id);
        $this->resetErrorBag();
        $this->ventId = $v->id;
        $this->ventAreaId = (int) $v->area_id;
        $this->ventNombre = $v->nombre;
    }

    public function cancelarVentanilla(): void
    {
        $this->resetErrorBag();
        $this->reset('ventId', 'ventNombre');
    }

    /** Crea o actualiza (si hay $ventId) una ventanilla. */
    public function guardarVentanilla(): void
    {
        $d = $this->validate([
            'ventAreaId' => ['required', 'exists:areas,id'],
            'ventNombre' => [
                'required',
                'string',
                'max:60',
                Rule::unique('ventanillas', 'nombre')
                    ->where('area_id', $this->ventAreaId)
                    ->ignore($this->ventId),
            ],
        ]);

        if ($this->ventId) {
            $v = Ventanilla::findOrFail($this->ventId);

            // Cambiar de área una ventanilla con historial movería sus turnos de lugar en las estadísticas
            if ((int) $v->area_id !== (int) $d['ventAreaId'] && Turno::where('ventanilla_id', $v->id)->exists()) {
                $this->addError('ventAreaId', 'No se puede cambiar de área una ventanilla con turnos registrados. Crea una nueva.');

                return;
            }

            $anterior = $v->nombre;
            $v->update(['area_id' => $d['ventAreaId'], 'nombre' => $d['ventNombre']]);
            $this->registrar('ventanilla_editada', "Ventanilla {$anterior} → {$v->nombre} (área {$v->area_id})");
        } else {
            $v = Ventanilla::create(['area_id' => $d['ventAreaId'], 'nombre' => $d['ventNombre']]);
            $this->registrar('ventanilla_creada', "Ventanilla {$v->nombre} (área {$v->area_id})");
        }

        $this->reset('ventId', 'ventNombre');
    }

    public function toggleVentanilla(int $id): void
    {
        $v = Ventanilla::findOrFail($id);

        if (
            $v->activa && Turno::where('ventanilla_id', $v->id)
            ->where('estado', EstadoTurno::Atendiendo->value)->exists()
        ) {
            $this->addError('ventNombre', 'La ventanilla tiene un turno en atención.');

            return;
        }

        $v->update(['activa' => ! $v->activa]);
        $this->registrar($v->activa ? 'ventanilla_activada' : 'ventanilla_desactivada', "Ventanilla {$v->nombre}");
    }

    /** Solo elimina una ventanilla que nunca tuvo turnos ni operadores asignados. */
    public function eliminarVentanilla(int $id): void
    {
        $v = Ventanilla::findOrFail($id);

        if (Turno::where('ventanilla_id', $v->id)->exists()) {
            $this->addError('ventNombre', "La ventanilla {$v->nombre} tiene turnos registrados y no puede eliminarse. Desactívala.");

            return;
        }

        if (User::where('ventanilla_id', $v->id)->exists()) {
            $this->addError('ventNombre', "La ventanilla {$v->nombre} tiene operadores asignados. Reasígnalos primero o desactívala.");

            return;
        }

        $detalle = "Ventanilla {$v->nombre} (área {$v->area_id})";
        $v->delete();

        if ($this->ventId === $id) {
            $this->reset('ventId', 'ventNombre');
        }

        $this->registrar('ventanilla_eliminada', $detalle);
    }

    public function render()
    {
        return view('livewire.admin.catalogo', [
            'areas' => Area::with('ventanillas')->orderBy('orden')->orderBy('nombre')->get(),
            'direcciones' => DB::table('direcciones')->orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }
}
