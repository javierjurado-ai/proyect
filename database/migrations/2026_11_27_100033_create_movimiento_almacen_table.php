<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento_almacen', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('tipo', ['ENTRADA_COMPRA', 'SALIDA_CURSO', 'DEVOLUCION_CURSO', 'TRASPASO', 'AJUSTE', 'BAJA']);
            $table->dateTime('fecha')->useCurrent();
            $table->integer('almacen_origen_id')->nullable();
            $table->integer('almacen_destino_id')->nullable();
            $table->integer('curso_id')->nullable();
            $table->integer('peticiones_logistica_id')->nullable();
            $table->integer('proveedor_id')->nullable();
            $table->integer('factura_empresa_proveedora_id')->nullable();
            $table->integer('persona_id');
            $table->string('referencia', 45)->nullable();
            $table->string('observaciones', 200)->nullable();

            $table->index(['almacen_origen_id'], 'fk_mov_origen_idx');
            $table->index(['almacen_destino_id'], 'fk_mov_destino_idx');
            $table->index(['curso_id'], 'fk_mov_curso_idx');
            $table->index(['peticiones_logistica_id'], 'fk_mov_peticion_idx');
            $table->index(['proveedor_id'], 'fk_mov_proveedor_idx');
            $table->index(['factura_empresa_proveedora_id'], 'fk_mov_fprov_idx');
            $table->index(['persona_id'], 'fk_mov_persona_idx');
            $table->index(['fecha'], 'idx_mov_fecha');

            $table->foreign('curso_id', 'fk_mov_curso')->references('id')->on('curso');
            $table->foreign('almacen_destino_id', 'fk_mov_destino')->references('id')->on('almacen');
            $table->foreign('factura_empresa_proveedora_id', 'fk_mov_fprov')->references('id')->on('factura_empresa_proveedora');
            $table->foreign('almacen_origen_id', 'fk_mov_origen')->references('id')->on('almacen');
            $table->foreign('persona_id', 'fk_mov_persona')->references('id')->on('persona');
            $table->foreign('peticiones_logistica_id', 'fk_mov_peticion')->references('id')->on('peticiones_logistica');
            $table->foreign('proveedor_id', 'fk_mov_proveedor')->references('id')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_almacen');
    }
};
