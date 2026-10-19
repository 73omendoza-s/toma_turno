<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PantallaContenido extends Model
{
    protected $table = 'pantalla_contenidos';

    protected $fillable = [
        'tipo',
        'archivo',
        'duracion_seg',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'duracion_seg' => 'integer',
            'orden' => 'integer',
        ];
    }

    /** Solo los contenidos activos, en el orden en que deben mostrarse. */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('id');
    }

    /** URL pública del archivo (requiere `php artisan storage:link`). */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->archivo);
    }

    public function esVideo(): bool
    {
        return $this->tipo === 'video';
    }
}
