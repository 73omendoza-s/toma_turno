<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('sesion_id', 100)->nullable();      // sesión que tiene hoy el usuario
            $t->timestamp('ultima_actividad')->nullable(); // última vez que esa sesión usó el sistema
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['sesion_id', 'ultima_actividad']);
        });
    }
};
