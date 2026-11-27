<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coste', function (Blueprint $table) {
            $table->integer('id', true);
            $table->decimal('directos', 10, 2)->default(0.00);
            $table->decimal('indirectos', 10, 2)->default(0.00);
            $table->decimal('organizacion', 10, 2)->default(0.00);
            $table->decimal('salariales', 10, 2)->default(0.00);
            $table->integer('curso_id');
            $table->integer('empresa_id');

            $table->unique(['curso_id', 'empresa_id'], 'uq_coste_curso_empresa');
            $table->index(['empresa_id'], 'fk_coste_empresa1_idx');

            $table->foreign('curso_id', 'fk_coste_curso1')->references('id')->on('curso');
            $table->foreign('empresa_id', 'fk_coste_empresa1')->references('id')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coste');
    }
};
