<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/** Pantalla pública de sala. Solo expone folio, área y ventanilla (sin datos personales). */
class TelevisorController extends Controller
{
    public function index(): View
    {
        return view('pantalla.tv');
    }

    public function datos(): JsonResponse
    {
        $llamados = Turno::with(['area:id,nombre', 'ventanilla:id,nombre'])
            ->hoy()
            ->whereNotNull('ventanilla_id')
            ->whereNotNull('llamado_en')
            ->orderByRaw('COALESCE(ultima_llamada_en, llamado_en) DESC')
            ->limit(7)
            ->get();

        $mapa = fn (Turno $t) => [
            'folio' => $t->folio,
            'area' => $t->area->nombre,
            'ventanilla' => $t->ventanilla->nombre,
            'estado' => $t->estado->value,
            // Cambia con cada llamada o re-llamada: el televisor lo usa para sonar el aviso.
            'token' => $t->id.'-'.$t->llamadas.'-'.($t->ultima_llamada_en ?? $t->llamado_en)->timestamp,
        ];

        $actual = $llamados->first();

        return response()->json([
            'actual' => $actual ? $mapa($actual) : null,
            'ultimos' => $llamados->skip(1)->values()->map($mapa),
        ]);
    }
}
