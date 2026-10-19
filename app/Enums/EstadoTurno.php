<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoTurno: string
{
    case Espera = 'espera';
    case Atendiendo = 'atendiendo';
    case Finalizado = 'finalizado';
    case Ausente = 'ausente';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Espera => 'En espera',
            self::Atendiendo => 'Atendiendo',
            self::Finalizado => 'Finalizado',
            self::Ausente => 'Ausente',
        };
    }
}
