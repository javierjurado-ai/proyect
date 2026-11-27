<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobro', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('factura_id');
            $table->date('fecha');
            $table->decimal('importe', 10, 2);
            $table->enum('forma_pago', ['TRANSFERENCIA', 'DOMICILIACION', 'TARJETA', 'EFECTIVO']);
            $table->string('referencia', 60)->nullable();

            $table->index(['factura_id'], 'fk_cobro_factura_idx');

            $table->foreign('factura_id', 'fk_cobro_factura')->references('id')->on('factura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobro');
    }
};
