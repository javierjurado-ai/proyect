<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_linea', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('factura_id');
            $table->string('concepto', 200);
            $table->enum('tipo_coste', ['DIRECTO', 'INDIRECTO', 'ORGANIZACION', 'OTRO'])->default('DIRECTO');
            $table->integer('curso_id')->nullable();
            $table->integer('articulo_id')->nullable();
            $table->decimal('cantidad', 10, 2)->default(1.00);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento_pct', 5, 2)->default(0.00);
            $table->decimal('tipo_iva', 5, 2)->default(21.00);
            $table->enum('exento', ['SI', 'NO'])->default('NO');
            $table->string('motivo_exencion', 150)->nullable();
            $table->decimal('base', 10, 2);
            $table->decimal('cuota_iva', 10, 2)->default(0.00);

            $table->index(['factura_id'], 'fk_flin_factura_idx');
            $table->index(['curso_id'], 'fk_flin_curso_idx');
            $table->index(['articulo_id'], 'fk_flin_articulo_idx');

            $table->foreign('articulo_id', 'fk_flin_articulo')->references('id')->on('articulo');
            $table->foreign('curso_id', 'fk_flin_curso')->references('id')->on('curso');
            $table->foreign('factura_id', 'fk_flin_factura')->references('id')->on('factura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_linea');
    }
};
