<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peticion_producto', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('articulo_id')->nullable();
            $table->string('producto', 100);
            $table->integer('cantidad');
            $table->integer('cantidad_servida')->default(0);
            $table->integer('cantidad_devuelta')->default(0);
            $table->integer('peticiones_logistica_id');

            $table->index(['peticiones_logistica_id'], 'fk_peticion_producto_peticiones_logistica1_idx');
            $table->index(['articulo_id'], 'fk_pet_prod_articulo_idx');

            $table->foreign('articulo_id', 'fk_pet_prod_articulo')->references('id')->on('articulo');
            $table->foreign('peticiones_logistica_id', 'fk_peticion_producto_peticiones_logistica1')->references('id')->on('peticiones_logistica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peticion_producto');
    }
};
