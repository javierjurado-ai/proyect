<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuesto', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('numero_alumnos');
            $table->string('nombre_formacion', 45);
            $table->double('horas_totales');
            $table->string('ubicacion_formacion', 90);
            $table->decimal('importe_bonificado', 10, 2);
            $table->date('fecha_bonificado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto');
    }
};
