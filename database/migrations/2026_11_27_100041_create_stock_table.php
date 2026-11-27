<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->integer('almacen_id');
            $table->integer('articulo_id');
            $table->integer('cantidad')->default(0);
            $table->primary(['almacen_id', 'articulo_id']);

            $table->index(['articulo_id'], 'fk_stock_articulo_idx');

            $table->foreign('almacen_id', 'fk_stock_almacen')->references('id')->on('almacen');
            $table->foreign('articulo_id', 'fk_stock_articulo')->references('id')->on('articulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
