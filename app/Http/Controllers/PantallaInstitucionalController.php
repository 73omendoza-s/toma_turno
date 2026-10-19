<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Institucional;

/** Versión de la identidad institucional: el televisor la consulta y se recarga si cambió. */
class PantallaInstitucionalController extends Controller
{
    public function __invoke()
    {
        return response()->json(['version' => Institucional::version()]);
    }
}
