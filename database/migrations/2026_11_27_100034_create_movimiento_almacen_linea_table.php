<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento_almacen_linea', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('movimiento_almacen_id');
            $table->integer('articulo_id');
            $table->integer('articulo_serie_id')->nullable();
            $table->integer('cantidad');
            $table->decimal('coste_unitario', 10, 2)->default(0.00);

            $table->index(['movimiento_almacen_id'], 'fk_mov_lin_mov_idx');
            $table->index(['articulo_id'], 'fk_mov_lin_articulo_idx');
            $table->index(['articulo_serie_id'], 'fk_mov_lin_serie_idx');

            $table->foreign('articulo_id', 'fk_mov_lin_articulo')->references('id')->on('articulo');
            $table->foreign('movimiento_almacen_id', 'fk_mov_lin_mov')->references('id')->on('movimiento_almacen');
            $table->foreign('articulo_serie_id', 'fk_mov_lin_serie')->references('id')->on('articulo_serie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_almacen_linea');
    }
};
