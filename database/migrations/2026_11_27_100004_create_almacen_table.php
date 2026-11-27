<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('almacen', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre', 60);
            $table->string('direccion', 150);
            $table->string('municipio', 45);
            $table->string('provincia', 45);
            $table->integer('centro_id')->nullable();
            $table->integer('responsable_id')->nullable();
            $table->boolean('activo')->default(true);

            $table->index(['centro_id'], 'fk_almacen_centro_idx');
            $table->index(['responsable_id'], 'fk_almacen_persona_idx');

            $table->foreign('centro_id', 'fk_almacen_centro')->references('id')->on('centro');
            $table->foreign('responsable_id', 'fk_almacen_persona')->references('id')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almacen');
    }
};
