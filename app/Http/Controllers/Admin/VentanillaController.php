<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VentanillaRequest;
use App\Models\Area;
use App\Models\Ventanilla;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VentanillaController extends Controller
{
    public function index(): View
    {
        $areas = Area::where('activa', true)->orderBy('orden')->orderBy('nombre')->get();
        $ventanillas = Ventanilla::with('area')->orderBy('area_id')->orderBy('id')->get();

        return view('admin.ventanillas', compact('areas', 'ventanillas'));
    }

    public function store(VentanillaRequest $request): RedirectResponse
    {
        Ventanilla::create($request->validated() + ['activa' => true]);

        return back()->with('ok', 'Ventanilla dada de alta.');
    }

    public function toggle(Ventanilla $ventanilla): RedirectResponse
    {
        $ventanilla->update(['activa' => ! $ventanilla->activa]);

        return back()->with('ok', $ventanilla->activa ? 'Ventanilla activada.' : 'Ventanilla desactivada.');
    }
}
