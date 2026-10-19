<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacora';

    public $timestamps = false;

    protected $fillable = [
        'rol',
        'usuario_id',
        'usuario_nombre',
        'accion',
        'detalle',
        'ip',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /**
     * Registra un evento de auditoría. Nunca debe romper la operación principal.
     */
    public static function registrar(
        string $rol,
        ?int $usuarioId,
        ?string $usuarioNombre,
        string $accion,
        ?string $detalle = null
    ): void {
        try {
            static::create([
                'rol' => $rol,
                'usuario_id' => $usuarioId,
                'usuario_nombre' => $usuarioNombre,
                'accion' => $accion,
                'detalle' => $detalle !== null ? mb_substr($detalle, 0, 255) : null,
                'ip' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
