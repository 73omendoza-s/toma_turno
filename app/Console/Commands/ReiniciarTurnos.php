<?php

namespace App\Console\Commands;

use App\Models\Turno;
use Illuminate\Console\Command;

class ReiniciarTurnos extends Command
{
    protected $signature = 'turnos:reiniciar';

    protected $description = 'Cancela los turnos abiertos de días anteriores. NO borra historial.';

    public function handle()
    {
        // El consecutivo reinicia solo cada día (tabla turno_contadores),
        // así que aquí únicamente se cierran los turnos que quedaron abiertos.
        $cancelados = Turno::whereIn('estado', ['pendiente', 'atendiendo'])
            ->where('fecha', '<', today()->toDateString())
            ->update(['estado' => 'cancelado']);

        $this->info("Turnos abiertos de días anteriores cancelados: {$cancelados}");

        return self::SUCCESS;
    }
}
