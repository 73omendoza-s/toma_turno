<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ventanilla extends Model
{
    protected $fillable = ['area_id', 'nombre', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    /** Solo las ventanillas activas, ordenadas por nombre. */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true)->orderBy('nombre');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }
}
