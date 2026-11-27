<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre_aula', 45);
            $table->integer('capacidad');
            $table->enum('estado', ['OCUPADA', 'DISPONIBLE']);
            $table->enum('uso_aula', ['TALLER', 'TEORICO']);
            $table->decimal('dimension', 10, 0);
            $table->string('observaciones', 450)->nullable();
            $table->integer('curso_id')->nullable();
            $table->integer('centro_id');

            $table->index(['curso_id'], 'fk_aulas_curso1_idx');
            $table->index(['centro_id'], 'fk_aulas_centro1_idx');

            $table->foreign('centro_id', 'fk_aulas_centro1')->references('id')->on('centro');
            $table->foreign('curso_id', 'fk_aulas_curso1')->references('id')->on('curso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
