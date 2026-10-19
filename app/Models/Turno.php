<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoTurno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Turno extends Model
{
    protected $fillable = [
        'area_id',
        'ventanilla_id',
        'user_id',
        'creado_por',
        'fecha',
        'numero',
        'folio',
        'estado',
        'llamadas',
        'llamado_en',
        'ultima_llamada_en',
        'finalizado_en',
        'espera_seg',
        'atencion_seg',
    ];

    // 'fecha' se maneja como texto Y-m-d a propósito (evita problemas de formato entre motores).
    protected function casts(): array
    {
        return [
            'estado' => EstadoTurno::class,
            'llamadas' => 'integer',
            'llamado_en' => 'datetime',
            'ultima_llamada_en' => 'datetime',
            'finalizado_en' => 'datetime',
            'espera_seg' => 'integer',
            'atencion_seg' => 'integer',
        ];
    }

    /* ---------- Scopes ---------- */

    public function scopeHoy(Builder $query): Builder
    {
        return $query->where('fecha', now()->toDateString());
    }

    public function scopeEnEspera(Builder $query): Builder
    {
        return $query->where('estado', EstadoTurno::Espera->value);
    }

    public function scopeAtendiendo(Builder $query): Builder
    {
        return $query->where('estado', EstadoTurno::Atendiendo->value);
    }

    public function scopeDeArea(Builder $query, int $areaId): Builder
    {
        return $query->where('area_id', $areaId);
    }

    /* ---------- Relaciones ---------- */

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(Ventanilla::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
