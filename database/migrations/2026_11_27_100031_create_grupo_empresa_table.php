<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_empresa', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('empresa_id');
            $table->integer('curso_id');

            $table->index(['empresa_id'], 'fk_grupo_empresa_empresa1_idx');
            $table->index(['curso_id'], 'fk_grupo_empresa_curso1_idx');

            $table->foreign('curso_id', 'fk_grupo_empresa_curso1')->references('id')->on('curso');
            $table->foreign('empresa_id', 'fk_grupo_empresa_empresa1')->references('id')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_empresa');
    }
};
