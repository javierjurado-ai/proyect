<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_coste_periodo', function (Blueprint $table) {
            $table->integer('id', true);
            $table->tinyInteger('mes');
            $table->decimal('importe', 10, 2);
            $table->integer('coste_id');

            $table->index(['coste_id'], 'fk_curso_coste_periodo_coste1_idx');

            $table->foreign('coste_id', 'fk_curso_coste_periodo_coste1')->references('id')->on('coste');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_coste_periodo');
    }
};
