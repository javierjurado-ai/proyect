<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_alumno', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('jornada_laboral', ['SI', 'NO']);
            $table->enum('certificado_afectado_terrorismo', ['SI', 'NO']);
            $table->enum('certificado_afectado_violencia_genero', ['SI', 'NO']);
            $table->enum('certificado_discapacidad', ['SI', 'NO']);
            $table->enum('nivel_estudio', ['MENOS_PRIMARIA', 'PRIMARIA', 'ESO_EGB', 'BACHILLERATO_FP_MEDIO', 'CP_NIVEL_3', 'FP_SUPERIOR', 'GRADO_DIPLOMATURA', 'LICENCIATURA_MASTER', 'DOCTORADO', 'OTRAS']);
            $table->enum('categoria_profesional', ['DIRECTIVO', 'MANDO_INTERMEDIO', 'TECNICO', 'TRABAJADOR_CUALIFICADO', 'TRABAJADOR_BAJA_CUALIFICACION']);
            $table->enum('erte', ['SI', 'NO']);
            $table->enum('fijo_discontinuo', ['SI', 'NO']);
            $table->enum('diploma', ['SI', 'NO']);
            $table->integer('curso_id');
            $table->integer('persona_id');
            $table->integer('empresa_id');

            $table->index(['curso_id'], 'fk_curso_alumno_curso1_idx');
            $table->index(['persona_id'], 'fk_curso_alumno_persona1_idx');
            $table->index(['empresa_id'], 'fk_curso_alumno_empresa1_idx');

            $table->foreign('curso_id', 'fk_curso_alumno_curso1')->references('id')->on('curso');
            $table->foreign('empresa_id', 'fk_curso_alumno_empresa1')->references('id')->on('empresa');
            $table->foreign('persona_id', 'fk_curso_alumno_persona1')->references('id')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_alumno');
    }
};
