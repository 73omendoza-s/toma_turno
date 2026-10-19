<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $t) {
            $t->id();
            $t->string('titulo', 80)->nullable();
            $t->string('subtitulo', 160)->nullable();
            $t->string('imagen', 255);                         // ruta relativa a public/, ej. img/banners/xxx.jpg
            $t->unsignedTinyInteger('ancho')->default(60);     // % del recuadro que ocupa el texto
            $t->string('ajuste', 10)->default('cubrir');       // cubrir | contener
            $t->unsignedInteger('orden')->default(0);
            $t->boolean('activo')->default(true);
            $t->timestamps();
        });

        // Los banners que hoy están escritos en tv.blade.php, para que la pantalla no cambie
        $ahora = now();
        $base = [
            ['img/banner1.png', 'Servicios Públicos',       'Trabajamos por una ciudad limpia y segura', 60],
            ['img/banner2.png', 'Estación de Transferencia', 'Separa tus residuos y deposítalos donde corresponde', 60],
            ['img/banner3.jpg', 'App La Paz',               'Reporta desde tu celular: alumbrado, fugas de agua, basura y más', 72],
            ['img/banner4.jpg', 'Visor Urbano',             'Catastro y adeudo predial en visorurbano.lapaz.gob.mx', 58],
            ['img/banner5.jpg', 'Tiburón Urbano',           'Tu transporte público en La Paz', 66],
            ['img/banner6.jpg', 'Espacios públicos',        'Parques y canchas', 40],
        ];
        foreach ($base as $i => [$imagen, $titulo, $subtitulo, $ancho]) {
            DB::table('banners')->insert([
                'titulo' => $titulo,
                'subtitulo' => $subtitulo,
                'imagen' => $imagen,
                'ancho' => $ancho,
                'ajuste' => 'cubrir',
                'orden' => $i + 1,
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
