<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Banners que rotan en la pantalla del televisor (/pantalla). */
class Banner extends Model
{
    protected $fillable = ['titulo', 'subtitulo', 'imagen', 'ancho', 'ajuste', 'orden', 'activo'];

    protected $casts = ['activo' => 'boolean', 'ancho' => 'integer', 'orden' => 'integer'];

    public function scopeActivos($q)
    {
        return $q->where('activo', true)->orderBy('orden')->orderBy('id');
    }

    /** URL pública de la imagen; el ?v= evita que el televisor use una versión vieja en caché. */
    public function getUrlAttribute(): string
    {
        return asset($this->imagen).'?v='.($this->updated_at?->timestamp ?? 0);
    }
}
