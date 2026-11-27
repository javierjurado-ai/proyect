<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articulo_serie', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('articulo_id');
            $table->string('numero_serie', 60);
            $table->integer('almacen_id')->nullable();
            $table->integer('curso_id')->nullable();
            $table->enum('estado', ['DISPONIBLE', 'PRESTADO', 'EN_REPARACION', 'BAJA'])->default('DISPONIBLE');
            $table->date('fecha_alta');

            $table->unique(['articulo_id', 'numero_serie'], 'uq_articulo_serie');
            $table->index(['almacen_id'], 'fk_articulo_serie_almacen_idx');
            $table->index(['curso_id'], 'fk_articulo_serie_curso_idx');

            $table->foreign('almacen_id', 'fk_articulo_serie_almacen')->references('id')->on('almacen');
            $table->foreign('articulo_id', 'fk_articulo_serie_articulo')->references('id')->on('articulo');
            $table->foreign('curso_id', 'fk_articulo_serie_curso')->references('id')->on('curso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articulo_serie');
    }
};
