<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_contacto', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('coordinador', 45);
            $table->tinyInteger('actividad');
            $table->integer('curso_id');
            $table->integer('persona_id');

            $table->index(['curso_id'], 'fk_curso_contacto_curso1_idx');
            $table->index(['persona_id'], 'fk_curso_contacto_persona1_idx');

            $table->foreign('curso_id', 'fk_curso_contacto_curso1')->references('id')->on('curso');
            $table->foreign('persona_id', 'fk_curso_contacto_persona1')->references('id')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_contacto');
    }
};
