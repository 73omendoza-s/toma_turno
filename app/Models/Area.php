<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['nombre', 'prefijo', 'activa', 'orden'];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'orden' => 'integer',
        ];
    }

    /** Solo las áreas activas, en el orden en que deben mostrarse. */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true)->orderBy('orden')->orderBy('id');
    }

    public function ventanillas(): HasMany
    {
        return $this->hasMany(Ventanilla::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }
}
