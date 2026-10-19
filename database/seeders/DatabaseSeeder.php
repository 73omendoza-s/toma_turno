<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Area;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // [nombre, prefijo, ventanillas]
        $catalogo = [
            ['Cajas', 'CAJ', ['Caja 1', 'Caja 2', 'Caja 3', 'Caja 4']],
            ['Subdirección de Recaudación', 'REC', ['Refacturación', 'Convenios']],
            ['Espectáculos Públicos', 'ESP', ['Ventanilla 1', 'Ventanilla 2']],
            ['SARE', 'SARE', ['Ventanilla 1', 'Ventanilla 2']],
            ['Giros Comerciales Restringidos', 'GCR', ['Ventanilla 1', 'Ventanilla 2']],
            ['Giros Comerciales Vía Pública', 'GVP', ['Ventanilla 1']],
            ['Desplegados de Impuesto Predial', 'PRE', ['Ventanilla 1', 'Ventanilla 2']],
            ['CISMUN', 'CIS', ['Ventanilla 1']], // supuesto: 1 ventanilla; se ajusta desde el panel de administración
        ];

        foreach ($catalogo as $i => [$nombre, $prefijo, $ventanillas]) {
            $area = Area::firstOrCreate(
                ['prefijo' => $prefijo],
                ['nombre' => $nombre, 'activa' => true, 'orden' => $i + 1]
            );

            foreach ($ventanillas as $v) {
                Ventanilla::firstOrCreate(['area_id' => $area->id, 'nombre' => $v], ['activa' => true]);
            }
        }

        $caja1 = Ventanilla::whereHas('area', fn ($q) => $q->where('prefijo', 'CAJ'))
            ->where('nombre', 'Caja 1')
            ->first();

        // Si no hay clave en el .env, se genera una aleatoria y se muestra una sola vez en consola.
        $usuarios = [
            ['admin', 'Administrador', env('SEED_ADMIN_PASSWORD'), Rol::Admin, null],
            ['recepcion', 'Operador de Recepción', env('SEED_RECEPCION_PASSWORD'), Rol::Recepcion, null],
            // Rol::Operador es el rol que usan los tests y el login (redirige a /operador)
            ['caja1', 'Operador Caja 1', env('SEED_CAJA1_PASSWORD'), Rol::Operador, $caja1?->id],
        ];

        foreach ($usuarios as [$usuario, $nombre, $password, $rol, $ventanillaId]) {
            $password = $password ?: Str::random(16);

            $u = User::firstOrCreate(
                ['usuario' => $usuario],
                [
                    'name' => $nombre,
                    'password' => $password,
                    'rol' => $rol,
                    'activo' => true,
                    'ventanilla_id' => $ventanillaId,
                ]
            );

            // Solo se muestra si el usuario se acaba de crear (los existentes no se tocan)
            if ($u->wasRecentlyCreated && $this->command) {
                $this->command->warn("Usuario {$usuario} creado. Clave: {$password}");
            }
        }
    }
}
