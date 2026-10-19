<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Ticket de turno para la impresora térmica de recepción (Epson TM-T88V, papel de 80 mm). */
class TicketController extends Controller
{
    public function show(Request $request, Turno $turno)
    {
        abort_unless(in_array($request->user()->rol->value, ['admin', 'recepcion'], true), 403);

        $turno->loadMissing('area');

        // Turnos de la misma área que están esperando antes que este
        $adelante = Turno::hoy()->enEspera()->deArea($turno->area_id)
            ->where('id', '<', $turno->id)->count();

        $direccion = $turno->area?->direccion_id
            ? DB::table('direcciones')->where('id', $turno->area->direccion_id)->value('nombre')
            : null;

        return view('ticket.ticket', [
            'turno' => $turno,
            'direccion' => $direccion,
            'adelante' => $adelante,
            'emitido' => $turno->created_at ?? now(),
        ]);
    }
}
