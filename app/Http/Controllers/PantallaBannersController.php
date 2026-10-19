<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Banner;

/** Lista pública (sin login) de banners activos para el televisor. */
class PantallaBannersController extends Controller
{
    public function __invoke()
    {
        return response()->json(
            Banner::activos()->get()->map(fn (Banner $b) => [
                'url' => $b->url,
                'titulo' => $b->titulo ?? '',
                'subtitulo' => $b->subtitulo ?? '',
                'ancho' => $b->ancho,
                'ajuste' => $b->ajuste,
            ])->values()
        );
    }
}
