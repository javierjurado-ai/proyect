<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_rlt', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('informe_rlt', ['SI', 'NO']);
            $table->enum('valor_informe', ['F', 'D']);
            $table->date('fecha_dis');
            $table->enum('resuelto_15', ['SI', 'NO']);
            $table->integer('empresa_id');
            $table->integer('curso_id');

            $table->unique(['curso_id', 'empresa_id'], 'uq_empresa_rlt_curso_empresa');
            $table->index(['empresa_id'], 'fk_empresa_rlt_empresa1_idx');

            $table->foreign('curso_id', 'fk_empresa_rlt_curso1')->references('id')->on('curso');
            $table->foreign('empresa_id', 'fk_empresa_rlt_empresa1')->references('id')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_rlt');
    }
};
