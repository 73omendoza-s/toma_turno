<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Bitacora;
use Illuminate\Support\Str;

trait RegistraBitacora
{
    /** Registra una acción administrativa usando el método estándar de auditoría. */
    protected function registrar(string $accion, string $detalle): void
    {
        $u = auth()->user();

        Bitacora::registrar(
            $u->rol->value,
            $u->id,
            Str::limit($u->name, 100, ''),
            Str::limit(strtoupper($accion), 60, ''),
            $detalle
        );
    }
}
