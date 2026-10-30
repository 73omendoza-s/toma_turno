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
        return view('pantalla.tv');   // resources/views/pantalla/tv.blade.php
    }

    public function datos(): JsonResponse
    {
        // El primero es el "actual"; el resto son los "últimos llamados". La cola de anuncios del
        // televisor usa toda la lista, por eso se traen 7 aunque en pantalla se vean solo 2 filas.
        $llamados = Turno::with(['area:id,nombre', 'ventanilla:id,nombre'])
            ->hoy()
            ->whereNotNull('ventanilla_id')
            ->whereNotNull('llamado_en')
            ->orderByRaw('COALESCE(ultima_llamada_en, llamado_en) DESC')
            ->orderByDesc('id')   // desempate: dos llamadas en el mismo segundo no cambian de orden entre sondeos
            ->limit(7)
            ->get();

        // ?-> y valores por defecto: una ventanilla o área borrada desde el catálogo no debe tumbar
        // la pantalla con un error 500 (el televisor mostraría "Sin conexión" hasta que ese turno salga de la lista).
        $mapa = fn (Turno $t) => [
            'folio' => $t->folio,
            'area' => $t->area?->nombre ?? 'Sin área',
            'ventanilla' => $t->ventanilla?->nombre ?? 'Ventanilla',
            'estado' => $t->estado?->value,
            // Cambia con cada llamada o re-llamada: el televisor lo usa para sonar el aviso.
            'token' => $t->id.'-'.$t->llamadas.'-'.(($t->ultima_llamada_en ?? $t->llamado_en)?->timestamp ?? 0),
        ];

        $actual = $llamados->first();

        return response()
            ->json([
                'actual' => $actual ? $mapa($actual) : null,
                'ultimos' => $llamados->skip(1)->values()->map($mapa),
            ])
            ->header('Cache-Control', 'no-store');
    }
}
