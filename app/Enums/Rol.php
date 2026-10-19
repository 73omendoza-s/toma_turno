<?php

declare(strict_types=1);

namespace App\Enums;

enum Rol: string
{
    case Admin = 'admin';
    case Supervisor = 'supervisor';
    case Recepcion = 'recepcion';
    case Operador = 'operador';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Supervisor => 'Supervisor (consulta)',
            self::Recepcion => 'Recepción',
            self::Operador => 'Operador de ventanilla',
        };
    }

    /** Ruta a la que llega cada rol después de iniciar sesión. */
    public function inicio(): string
    {
        return match ($this) {
            self::Admin => '/admin/catalogo',
            self::Supervisor => '/admin/estadisticas',
            self::Recepcion => '/recepcion',
            self::Operador => '/operador',
        };
    }
}
