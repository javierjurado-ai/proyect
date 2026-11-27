<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('serie_factura_id');
            $table->integer('numero')->nullable();
            $table->enum('tipo', ['ORDINARIA', 'RECTIFICATIVA'])->default('ORDINARIA');
            $table->integer('factura_rectificada_id')->nullable();
            $table->string('motivo_rectificacion', 200)->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('empresa_id');
            $table->integer('curso_id')->nullable();
            $table->string('cliente_cif', 15)->nullable();
            $table->string('cliente_nombre', 100)->nullable();
            $table->string('cliente_direccion', 250)->nullable();
            $table->decimal('base_imponible', 10, 2)->default(0.00);
            $table->decimal('total_iva', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->enum('forma_pago', ['TRANSFERENCIA', 'DOMICILIACION', 'TARJETA', 'EFECTIVO'])->default('TRANSFERENCIA');
            $table->enum('estado', ['BORRADOR', 'EMITIDA', 'COBRADA_PARCIAL', 'COBRADA', 'RECTIFICADA'])->default('BORRADOR');
            $table->char('hash', 64)->nullable();
            $table->char('hash_anterior', 64)->nullable();
            $table->integer('persona_id');
            $table->string('observaciones', 300)->nullable();

            $table->unique(['serie_factura_id', 'numero'], 'uq_factura_serie_numero');
            $table->index(['empresa_id'], 'fk_factura_empresa_idx');
            $table->index(['curso_id'], 'fk_factura_curso_idx');
            $table->index(['factura_rectificada_id'], 'fk_factura_rect_idx');
            $table->index(['persona_id'], 'fk_factura_persona_idx');

            $table->foreign('curso_id', 'fk_factura_curso')->references('id')->on('curso');
            $table->foreign('empresa_id', 'fk_factura_empresa')->references('id')->on('empresa');
            $table->foreign('persona_id', 'fk_factura_persona')->references('id')->on('persona');
            $table->foreign('factura_rectificada_id', 'fk_factura_rect')->references('id')->on('factura');
            $table->foreign('serie_factura_id', 'fk_factura_serie')->references('id')->on('serie_factura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura');
    }
};
