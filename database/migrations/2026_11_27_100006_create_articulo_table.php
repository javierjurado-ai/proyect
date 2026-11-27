<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articulo', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('codigo', 20);
            $table->string('nombre', 100);
            $table->string('descripcion', 300)->nullable();
            $table->enum('tipo', ['EQUIPO', 'FUNGIBLE', 'MATERIAL_DIDACTICO', 'MOBILIARIO', 'OTRO']);
            $table->enum('unidad', ['UNIDAD', 'CAJA', 'PAQUETE', 'LICENCIA'])->default('UNIDAD');
            $table->enum('retornable', ['SI', 'NO'])->default('SI');
            $table->enum('controla_serie', ['SI', 'NO'])->default('NO');
            $table->decimal('precio_coste', 10, 2)->default(0.00);
            $table->decimal('precio_venta', 10, 2)->nullable();
            $table->decimal('tipo_iva', 5, 2)->default(21.00);
            $table->integer('stock_minimo')->default(0);
            $table->integer('proveedor_habitual_id')->nullable();
            $table->boolean('activo')->default(true);

            $table->unique(['codigo'], 'uq_articulo_codigo');
            $table->index(['proveedor_habitual_id'], 'fk_articulo_proveedor_idx');

            $table->foreign('proveedor_habitual_id', 'fk_articulo_proveedor')->references('id')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articulo');
    }
};
