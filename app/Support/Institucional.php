<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Configuracion;

/**
 * Identidad institucional: nombre, colores y logos que usan todas las pantallas.
 * Se administra en /admin/institucional; si no hay valor guardado se usan los de DEFAULTS.
 */
class Institucional
{
    public const TEXTOS = [
        'inst_nombre' => 'Nombre de la institución',
        'inst_subtitulo' => 'Dependencia o área (subtítulo)',
        'inst_lema' => 'Lema (aparece en el ticket)',
    ];

    public const COLORES = [
        'color_primario' => 'Color principal',
        'color_primario_osc' => 'Color principal oscuro',
        'color_acento' => 'Color de acento (dorado)',
        'color_acento_claro' => 'Acento claro',
        'color_crema_1' => 'Fondo claro 1',
        'color_crema_2' => 'Fondo claro 2',
    ];

    public const DEFAULTS = [
        'inst_nombre' => 'XVIII Ayuntamiento de La Paz',
        'inst_subtitulo' => 'TESORERÍA GENERAL',
        'inst_lema' => '“Es La Paz”',
        'color_primario' => '#9B2247',
        'color_primario_osc' => '#611232',
        'color_acento' => '#A57F2C',
        'color_acento_claro' => '#E3C77A',
        'color_crema_1' => '#FBF5EC',
        'color_crema_2' => '#EBDCC4',
        'logo_escudo' => 'img/escudo.png',
        'logo_secundario' => 'img/lapaz_v2.png',
    ];

    public static function get(string $clave): string
    {
        return Configuracion::valor($clave, self::DEFAULTS[$clave]);
    }

    public static function valido(string $hex): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $hex);
    }

    /** Color guardado; si por cualquier motivo no es válido, el original. */
    public static function color(string $clave): string
    {
        $v = self::get($clave);

        return self::valido($v) ? $v : self::DEFAULTS[$clave];
    }

    public static function rgb(string $hex): string
    {
        $h = ltrim($hex, '#');

        return hexdec(substr($h, 0, 2)).','.hexdec(substr($h, 2, 2)).','.hexdec(substr($h, 4, 2));
    }

    /** Variables CSS que usan todas las vistas: var(--guinda), var(--dorado)... */
    public static function css(): string
    {
        $p = self::color('color_primario');
        $po = self::color('color_primario_osc');

        return ':root{'
            .'--guinda:'.$p.';'
            .'--guinda-osc:'.$po.';'
            .'--dorado:'.self::color('color_acento').';'
            .'--dorado-claro:'.self::color('color_acento_claro').';'
            .'--crema-1:'.self::color('color_crema_1').';'
            .'--crema-2:'.self::color('color_crema_2').';'
            .'--guinda-rgb:'.self::rgb($p).';'
            .'--guinda-osc-rgb:'.self::rgb($po).';'
            .'}';
    }

    /** URL pública del logo; el ?v= evita que el navegador muestre uno viejo en caché. */
    public static function logo(string $clave): string
    {
        return asset(self::get($clave)).'?v='.self::version();
    }

    /** Cambia cada vez que se guarda algo; el televisor lo consulta para recargarse solo. */
    public static function version(): string
    {
        $v = Configuracion::query()->whereIn('clave', array_keys(self::DEFAULTS))->max('updated_at');

        return (string) ($v ?: '0');
    }

    public static function luminancia(string $hex): float
    {
        [$r, $g, $b] = array_map(function ($c) {
            $c = ((int) $c) / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, explode(',', self::rgb($hex)));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /** Relación de contraste WCAG entre dos colores (1 a 21). */
    public static function contraste(string $a, string $b): float
    {
        $la = self::luminancia($a);
        $lb = self::luminancia($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }
}
