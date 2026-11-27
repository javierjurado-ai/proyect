<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('usuario', 45);
            $table->string('contrasena', 255);
            $table->integer('persona_id');

            $table->index(['persona_id'], 'fk_usuario_persona1_idx');

            $table->foreign('persona_id', 'fk_usuario_persona1')->references('id')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
