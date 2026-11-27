<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_calendario', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('curso_id');
            $table->date('fecha');
            $table->time('hora_inicio1')->nullable();
            $table->time('hora_fin1')->nullable();
            $table->time('hora_inicio2')->nullable();
            $table->time('hora_fin2')->nullable();

            $table->index(['curso_id'], 'fk_curso_calendario_curso1_idx');

            $table->foreign('curso_id', 'fk_curso_calendario_curso1')->references('id')->on('curso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_calendario');
    }
};
