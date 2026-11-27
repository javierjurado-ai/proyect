<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serie_factura', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('codigo', 10);
            $table->string('descripcion', 60);
            $table->smallInteger('anio');
            $table->integer('ultimo_numero')->default(0);

            $table->unique(['codigo', 'anio'], 'uq_serie_anio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serie_factura');
    }
};
