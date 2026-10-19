<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AreaRequest;
use App\Models\Area;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AreaController extends Controller
{
    public function index(): View
    {
        $areas = Area::withCount('ventanillas')->orderBy('orden')->orderBy('nombre')->get();

        return view('admin.areas', compact('areas'));
    }

    public function store(AreaRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['orden'] = $datos['orden'] ?? ((int) Area::max('orden') + 1);

        Area::create($datos + ['activa' => true]);

        return back()->with('ok', 'Área dada de alta.');
    }

    /** Baja lógica: no se borran áreas porque los turnos históricos dependen de ellas. */
    public function toggle(Area $area): RedirectResponse
    {
        $area->update(['activa' => ! $area->activa]);

        return back()->with('ok', $area->activa ? 'Área activada.' : 'Área desactivada.');
    }
}
