<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_retribucion', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('tipo', ['PRECIO HORA', 'PRECIO FIJO', 'BRUTO MENSUAL 12', 'BRUTO MENSUAL 14', 'BRUTO ANUAL']);
            $table->double('seguridad_social');
            $table->double('importe');
            $table->date('fecha');
            $table->integer('curso_docente_id');

            $table->index(['curso_docente_id'], 'fk_tipo_retribucion_curso_docente1_idx');

            $table->foreign('curso_docente_id', 'fk_tipo_retribucion_curso_docente1')->references('id')->on('curso_docente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_retribucion');
    }
};
