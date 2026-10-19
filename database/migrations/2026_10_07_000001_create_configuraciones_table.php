<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $t) {
            $t->string('clave', 60)->primary();
            $t->string('valor', 255);
            $t->timestamps();
        });

        $ahora = now();
        DB::table('configuraciones')->insert([
            // Minutos de atención tras los cuales se avisa al operador
            ['clave' => 'tiempo_atencion_min', 'valor' => '15', 'created_at' => $ahora, 'updated_at' => $ahora],
            // Si el operador pide más tiempo, minutos antes de volver a avisarle
            ['clave' => 'tiempo_extension_min', 'valor' => '5', 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
