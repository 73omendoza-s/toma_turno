<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impresora_config', function (Blueprint $t) {
            $t->id();
            $t->string('modelo', 20)->default('tm-t88v');     // tm-t88v | tm-t88vii
            $t->string('conexion', 20)->default('windows');   // windows (USB compartida) | network | archivo
            $t->string('destino', 150)->nullable();           // nombre compartido o IP
            $t->boolean('directa')->default(false);           // impresión directa ESC/POS
            $t->boolean('corte')->default(true);              // cortar papel al final
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impresora_config');
    }
};
