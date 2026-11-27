<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_docente', function (Blueprint $table) {
            $table->integer('id', true);
            $table->decimal('numero_horas', 6, 2);
            $table->integer('persona_id');
            $table->integer('empresa_id');
            $table->integer('curso_id');
            $table->enum('catagoria', ['CUENTA AJENA', 'AUTONOMO']);
            $table->enum('tipo_contrato', ['INDEFINIDO', 'FIJO DISCONTINUO', 'OBRA Y SERVICIO', 'TIEMPO COMPLETO']);
            $table->string('convenio', 45)->default('FORMACION NO REGLADA');
            $table->string('categoria', 45)->default('DOCENTE');
            $table->string('IBAN', 45);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->double('horas_presenciales')->nullable();
            $table->double('horas_virtuales')->nullable();

            $table->index(['persona_id'], 'fk_curso_docente_persona1_idx');
            $table->index(['empresa_id'], 'fk_curso_docente_empresa1_idx');
            $table->index(['curso_id'], 'fk_curso_docente_curso1_idx');

            $table->foreign('curso_id', 'fk_curso_docente_curso1')->references('id')->on('curso');
            $table->foreign('empresa_id', 'fk_curso_docente_empresa1')->references('id')->on('empresa');
            $table->foreign('persona_id', 'fk_curso_docente_persona1')->references('id')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_docente');
    }
};
