<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('prefijo', 5)->unique();      // CAJ, REC, SARE...
            $table->boolean('activa')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('ventanillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas');
            $table->string('nombre', 60);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['area_id', 'nombre'], 'ventanillas_area_nombre_uq');
        });

        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('ventanilla_id')->nullable()->constrained('ventanillas');
            $table->foreignId('user_id')->nullable()->constrained('users');      // operador que atiende
            $table->foreignId('creado_por')->nullable()->constrained('users');   // recepción que emitió
            $table->date('fecha');
            $table->unsignedInteger('numero');
            $table->string('folio', 12);
            $table->string('estado', 15);                  // espera, atendiendo, finalizado, ausente
            $table->unsignedSmallInteger('llamadas')->default(0);
            $table->timestamp('llamado_en')->nullable();
            $table->timestamp('ultima_llamada_en')->nullable();
            $table->timestamp('finalizado_en')->nullable();
            $table->unsignedInteger('espera_seg')->nullable();
            $table->unsignedInteger('atencion_seg')->nullable();
            $table->timestamps();

            // Impide dos turnos con el mismo número en el mismo día y área (aunque dos recepciones saquen turno a la vez).
            $table->unique(['area_id', 'fecha', 'numero'], 'turnos_area_fecha_num_uq');
            $table->index(['fecha', 'area_id', 'estado'], 'turnos_cola_idx');
            $table->index(['user_id', 'fecha'], 'turnos_user_fecha_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('ventanilla_id')->nullable()->constrained('ventanillas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ventanilla_id');
        });
        Schema::dropIfExists('turnos');
        Schema::dropIfExists('ventanillas');
        Schema::dropIfExists('areas');
    }
};
