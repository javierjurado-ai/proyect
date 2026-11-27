<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_calendario', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('turno', ['MAÑANA', 'TARDE', 'MIXTO']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('dia_semana', ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO']);
            $table->integer('curso_docente_id');

            $table->index(['curso_docente_id'], 'fk_docente_calendario_curso_docente1_idx');

            $table->foreign('curso_docente_id', 'fk_docente_calendario_curso_docente1')->references('id')->on('curso_docente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_calendario');
    }
};
