<?php

declare(strict_types=1);

namespace App\Support;

final class Duracion
{
    /** Segundos -> "12 min 05 s". Null -> "—". */
    public static function formato(float|int|string|null $segundos): string
    {
        if ($segundos === null || $segundos === '') {
            return '—';
        }

        $total = (int) round((float) $segundos);

        return intdiv($total, 60).' min '.str_pad((string) ($total % 60), 2, '0', STR_PAD_LEFT).' s';
    }
}
