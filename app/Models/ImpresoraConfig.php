<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Configuración de la impresora térmica (una sola fila). Si no existe, se usa el .env / config/turnos.php. */
class ImpresoraConfig extends Model
{
    protected $table = 'impresora_config';

    protected $fillable = ['modelo', 'conexion', 'destino', 'directa', 'corte'];

    protected $casts = ['directa' => 'boolean', 'corte' => 'boolean'];

    public const MODELOS = [
        'tm-t88v' => [
            'nombre' => 'Epson TM-T88V',
            'compartida' => 'TM-T88V',
            'red' => 'Solo si tiene la tarjeta Ethernet opcional (UB-E04 u otra).',
        ],
        'tm-t88vii' => [
            'nombre' => 'Epson TM-T88VII',
            'compartida' => 'TM-T88VII',
            'red' => 'Trae Ethernet y USB de fábrica.',
        ],
    ];

    /** @return array{modelo:string,conexion:string,destino:string,directa:bool,corte:bool} */
    public static function actual(): array
    {
        try {
            $r = static::query()->first();
        } catch (\Throwable) {
            $r = null; // la tabla aún no existe
        }

        if (! $r) {
            return [
                'modelo' => 'tm-t88v',
                'conexion' => (string) config('turnos.impresora_tipo', 'archivo'),
                'destino' => (string) config('turnos.impresora_destino', ''),
                'directa' => (bool) config('turnos.impresion_directa', false),
                'corte' => true,
            ];
        }

        return [
            'modelo' => (string) $r->modelo,
            'conexion' => (string) $r->conexion,
            'destino' => (string) $r->destino,
            'directa' => (bool) $r->directa,
            'corte' => (bool) $r->corte,
        ];
    }

    public static function directa(): bool
    {
        return static::actual()['directa'];
    }
}
