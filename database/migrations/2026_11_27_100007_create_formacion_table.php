<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formacion', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre', 100);
            $table->integer('codigo_accion')->nullable();
            $table->double('horas_totales')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->integer('area_profesional_id');

            $table->index(['area_profesional_id'], 'fk_formacion_area_profesional1_idx');

            $table->foreign('area_profesional_id', 'fk_formacion_area_profesional1')->references('id')->on('area_profesional');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formacion');
    }
};
