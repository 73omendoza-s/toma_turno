<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Area;
use App\Models\ImpresoraConfig;
use App\Models\Turno;
use App\Services\ImpresoraTicket;
use App\Services\TurnoService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
#[Title('Recepción')]
class Recepcion extends Component
{
    public ?int $ultimoId = null;

    public ?string $error = null;

    public function emitir(int $areaId, TurnoService $servicio): void
    {
        $this->error = null;

        try {
            $turno = $servicio->emitir($areaId, auth()->user());
            $this->ultimoId = $turno->id;
        } catch (Throwable $e) {
            report($e);
            $this->error = 'No se pudo emitir el turno. Intenta de nuevo.';

            return;
        }

        $this->imprimirTurno($turno);
    }

    /** Vuelve a imprimir el último ticket emitido (papel atascado, ticket perdido). */
    public function reimprimir(): void
    {
        $this->error = null;

        if ($this->ultimoId && ($turno = Turno::find($this->ultimoId))) {
            $this->imprimirTurno($turno);
        }
    }

    /** Directo a la térmica si está activada la impresión directa (módulo /admin/impresora); si no, el método anterior (iframe del navegador). */
    protected function imprimirTurno(Turno $turno): void
    {
        if (! ImpresoraConfig::directa()) {
            $this->dispatch('imprimir-ticket', url: route('ticket.imprimir', $turno->id));

            return;
        }

        try {
            app(ImpresoraTicket::class)->imprimir($turno);
        } catch (Throwable $e) {
            report($e);
            $this->error = 'El turno se emitió, pero no se pudo imprimir. Revise la impresora y use "Reimprimir ticket".';
        }
    }

    public function render()
    {
        return view('livewire.recepcion', [
            'areas' => Area::activas()->get(),
            'direcciones' => DB::table('direcciones')->orderBy('orden')->orderBy('nombre')->get(),
            'esperando' => Turno::hoy()->enEspera()
                ->selectRaw('area_id, count(*) as total')
                ->groupBy('area_id')
                ->pluck('total', 'area_id'),
            'ultimo' => $this->ultimoId ? Turno::with('area')->find($this->ultimoId) : null,
        ]);
    }
}
