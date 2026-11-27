<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centro', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre', 45);
            $table->string('direccion', 45);
            $table->string('telefono', 45);
            $table->string('email', 45);
            $table->string('provincia', 45);
            $table->string('municipio', 45);
            $table->char('codigo_postal', 5);
            $table->integer('numero_total_aula');
            $table->enum('tipo_centro', ['INTERNO', 'EXTERNO']);
            $table->integer('empresa_id')->nullable();

            $table->index(['empresa_id'], 'fk_centro_empresa1_idx');

            $table->foreign('empresa_id', 'fk_centro_empresa1')->references('id')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centro');
    }
};
