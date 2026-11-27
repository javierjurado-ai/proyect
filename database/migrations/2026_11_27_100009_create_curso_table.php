<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('codigo_accion', 45);
            $table->string('codigo_grupo', 45);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->time('hora_inicio');
            $table->time('hora_final');
            $table->enum('turno', ['MAÑANA', 'TARDE', 'PARTIDO']);
            $table->string('aula', 45);
            $table->string('tecnico_comunidad', 45);
            $table->string('observaciones', 200)->nullable();
            $table->string('objetivos', 500);
            $table->string('contenido', 1000);
            $table->decimal('horas_presenciales', 6, 2)->nullable();
            $table->decimal('horas_teleformacion', 6, 2)->nullable();
            $table->enum('modalidad', ['PRESENCIAL', 'TELEFORMACION', 'MIXTA']);
            $table->tinyInteger('nivel_formacion')->default(0);
            $table->integer('centro_id')->nullable();
            $table->integer('presupuesto_id')->nullable();
            $table->integer('formacion_id')->nullable();
            $table->integer('empresa_id');

            $table->index(['centro_id'], 'fk_curso_centro_idx');
            $table->index(['presupuesto_id'], 'fk_curso_presupuesto1_idx');
            $table->index(['formacion_id'], 'fk_curso_formacion1_idx');
            $table->index(['empresa_id'], 'fk_curso_empresa1_idx');

            $table->foreign('centro_id', 'fk_curso_centro')->references('id')->on('centro');
            $table->foreign('empresa_id', 'fk_curso_empresa1')->references('id')->on('empresa');
            $table->foreign('formacion_id', 'fk_curso_formacion1')->references('id')->on('formacion');
            $table->foreign('presupuesto_id', 'fk_curso_presupuesto1')->references('id')->on('presupuesto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso');
    }
};
