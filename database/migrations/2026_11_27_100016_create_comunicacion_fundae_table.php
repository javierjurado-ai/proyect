<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunicacion_fundae', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('tipo', ['INICIO', 'FINALIZACION', 'MODIFICACION', 'CANCELACION']);
            $table->date('fecha_limite');
            $table->date('fecha_envio');
            $table->integer('curso_id')->nullable();

            $table->index(['curso_id'], 'fk_comunicacion_fundae_curso1_idx');

            $table->foreign('curso_id', 'fk_comunicacion_fundae_curso1')->references('id')->on('curso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunicacion_fundae');
    }
};
