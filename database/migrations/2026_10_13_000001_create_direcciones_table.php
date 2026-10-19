<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 100)->unique();
            $t->boolean('activa')->default(true);
            $t->unsignedInteger('orden')->default(0);
            $t->timestamps();
        });

        // Cada área pertenece a una dirección; cada supervisor ve solo la suya
        Schema::table('areas', function (Blueprint $t) {
            $t->unsignedBigInteger('direccion_id')->nullable()->index();
        });
        Schema::table('users', function (Blueprint $t) {
            $t->unsignedBigInteger('direccion_id')->nullable()->index();
        });

        $ahora = now();
        DB::table('direcciones')->insert([
            ['nombre' => 'Dirección de Ingresos',  'activa' => true, 'orden' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Dirección de Comercio',  'activa' => true, 'orden' => 2, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('direccion_id'));
        Schema::table('areas', fn (Blueprint $t) => $t->dropColumn('direccion_id'));
        Schema::dropIfExists('direcciones');
    }
};
