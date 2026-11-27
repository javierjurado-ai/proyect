<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centro_contactos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre', 45);
            $table->enum('contacto', ['COORDINADOR', 'RESPONSABLE']);
            $table->string('telefono_personal', 45);
            $table->string('email_personal', 45);
            $table->integer('centro_id')->nullable();
            $table->integer('curso_id')->nullable();

            $table->index(['centro_id'], 'fk_centro_contactos_centro1_idx');
            $table->index(['curso_id'], 'fk_centro_contactos_curso1_idx');

            $table->foreign('centro_id', 'fk_centro_contactos_centro1')->references('id')->on('centro');
            $table->foreign('curso_id', 'fk_centro_contactos_curso1')->references('id')->on('curso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centro_contactos');
    }
};
