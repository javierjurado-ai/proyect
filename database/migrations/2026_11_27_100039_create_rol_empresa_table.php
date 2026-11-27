<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_empresa', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('rol_id');
            $table->integer('empresa_id');

            $table->unique(['empresa_id', 'rol_id'], 'uq_rol_empresa');
            $table->index(['rol_id'], 'fk_rol_empresa_rol1_idx');
            $table->index(['empresa_id'], 'fk_rol_empresa_empresa1_idx');

            $table->foreign('empresa_id', 'fk_rol_empresa_empresa1')->references('id')->on('empresa');
            $table->foreign('rol_id', 'fk_rol_empresa_rol1')->references('id')->on('rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_empresa');
    }
};
