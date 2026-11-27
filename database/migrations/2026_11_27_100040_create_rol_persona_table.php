<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_persona', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('rol_id');
            $table->integer('persona_id');

            $table->unique(['persona_id', 'rol_id'], 'uq_rol_persona');
            $table->index(['rol_id'], 'fk_rol_persona_rol1_idx');
            $table->index(['persona_id'], 'fk_rol_persona_persona1_idx');

            $table->foreign('persona_id', 'fk_rol_persona_persona1')->references('id')->on('persona');
            $table->foreign('rol_id', 'fk_rol_persona_rol1')->references('id')->on('rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_persona');
    }
};
