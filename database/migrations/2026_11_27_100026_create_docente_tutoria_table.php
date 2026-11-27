<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_tutoria', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('tipo_tutoria', ['CORREO_ELECTRONICO', 'VIDEOCONFERENCIA', 'FORO', 'OTRAS']);
            $table->string('descripcion', 50)->nullable();
            $table->integer('curso_docente_id');

            $table->index(['curso_docente_id'], 'fk_docente_tutoria_curso_docente1_idx');

            $table->foreign('curso_docente_id', 'fk_docente_tutoria_curso_docente1')->references('id')->on('curso_docente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_tutoria');
    }
};
