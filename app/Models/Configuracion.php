<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Parámetros del sistema administrables desde /admin/configuracion (clave => valor). */
class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $primaryKey = 'clave';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['clave', 'valor'];

    public static function valor(string $clave, string|int $porDefecto): string
    {
        return (string) Cache::remember("config.$clave", 300, fn () => static::query()->where('clave', $clave)->value('valor') ?? $porDefecto);
    }

    public static function entero(string $clave, int $porDefecto): int
    {
        return (int) static::valor($clave, $porDefecto);
    }

    public static function guardar(string $clave, string|int $valor): void
    {
        static::query()->updateOrInsert(
            ['clave' => $clave],
            ['valor' => (string) $valor, 'updated_at' => now(), 'created_at' => now()]
        );
        Cache::forget("config.$clave");
    }
}
