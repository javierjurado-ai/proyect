<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_aula_virtual', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('curso_id');
            $table->string('medio', 100);
            $table->string('conexion', 100);
            $table->string('contacto', 100);
            $table->char('telefono', 9);
            $table->enum('bimodal', ['SI', 'NO'])->default('NO');
            $table->enum('sin_participantes_en_centro', ['SI', 'NO'])->default('NO');
            $table->enum('sin_docentes_en_centro', ['SI', 'NO'])->default('NO');

            $table->unique(['curso_id'], 'uq_curso_aula_virtual_curso');

            $table->foreign('curso_id', 'fk_curso_aula_virtual_curso1')->references('id')->on('curso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_aula_virtual');
    }
};
