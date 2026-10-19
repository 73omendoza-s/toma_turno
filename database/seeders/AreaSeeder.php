<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            ['Cajas', 'CAJ', ['Caja 1', 'Caja 2', 'Caja 3', 'Caja 4']],
            ['Subdirección de Recaudación', 'REC', ['Refacturación', 'Convenios']],
            ['Espectáculos Públicos', 'ESP', ['Ventanilla 1', 'Ventanilla 2']],
            ['SARE', 'SARE', ['Ventanilla 1', 'Ventanilla 2']],
            ['Giros Comerciales Restringidos', 'GCR', ['Ventanilla 1', 'Ventanilla 2']],
            ['Giros Comerciales Vía Pública', 'GVP', ['Ventanilla 1']],
            ['Desplegados de Impuesto Predial', 'PRE', ['Ventanilla 1', 'Ventanilla 2']],
            ['CISMUN', 'CIS', ['Ventanilla 1']],
        ];

        foreach ($catalogo as $i => [$nombre, $prefijo, $ventanillas]) {
            $area = Area::updateOrCreate(
                ['prefijo' => $prefijo],
                ['nombre' => $nombre, 'orden' => $i + 1]
            );
            foreach ($ventanillas as $v) {
                $area->ventanillas()->updateOrCreate(['nombre' => $v]);
            }
        }

        User::updateOrCreate(['usuario' => 'admin'], [
            'name' => 'Administrador',
            'password' => 'Cambiar123*',
            'rol' => 'admin',
        ]);
        User::updateOrCreate(['usuario' => 'recepcion'], [
            'name' => 'Recepción',
            'password' => 'Cambiar123*',
            'rol' => 'recepcion',
        ]);

        User::updateOrCreate(['usuario' => 'supervisor'], [
            'name' => 'Supervisor',
            'password' => 'Cambiar123*',
            'rol' => 'supervisor',
        ]);
    }
}
