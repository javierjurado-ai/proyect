<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peticion_mensajes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('mensaje', 500);
            $table->date('fecha_creada');
            $table->time('hora_creada');
            $table->integer('persona_id');

            $table->index(['persona_id'], 'fk_peticion_mensajes_persona1_idx');

            $table->foreign('persona_id', 'fk_peticion_mensajes_persona1')->references('id')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peticion_mensajes');
    }
};
