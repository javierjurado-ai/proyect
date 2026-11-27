<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_empresa_proveedora_linea', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('factura_empresa_proveedora_id');
            $table->string('concepto', 200);
            $table->enum('tipo_coste', ['DIRECTO', 'INDIRECTO', 'ORGANIZACION', 'OTRO'])->default('DIRECTO');
            $table->integer('curso_id')->nullable();
            $table->integer('articulo_id')->nullable();
            $table->decimal('cantidad', 10, 2)->default(1.00);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento_pct', 5, 2)->default(0.00);
            $table->decimal('tipo_iva', 5, 2)->default(21.00);
            $table->enum('exento', ['SI', 'NO'])->default('NO');
            $table->decimal('base', 10, 2);
            $table->decimal('cuota_iva', 10, 2)->default(0.00);

            $table->index(['factura_empresa_proveedora_id'], 'fk_fprov_lin_factura_idx');
            $table->index(['curso_id'], 'fk_fprov_lin_curso_idx');
            $table->index(['articulo_id'], 'fk_fprov_lin_articulo_idx');

            $table->foreign('articulo_id', 'fk_fprov_lin_articulo')->references('id')->on('articulo');
            $table->foreign('curso_id', 'fk_fprov_lin_curso')->references('id')->on('curso');
            $table->foreign('factura_empresa_proveedora_id', 'fk_fprov_lin_factura')->references('id')->on('factura_empresa_proveedora');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_empresa_proveedora_linea');
    }
};
