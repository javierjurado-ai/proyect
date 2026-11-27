<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credito_formativo', function (Blueprint $table) {
            $table->integer('id', true);
            $table->smallInteger('año');
            $table->decimal('importe_total', 10, 2);
            $table->decimal('importe_consumido', 10, 2);
            $table->integer('empresa_id');

            $table->index(['empresa_id'], 'fk_credito_formativo_empresa1_idx');

            $table->foreign('empresa_id', 'fk_credito_formativo_empresa1')->references('id')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credito_formativo');
    }
};
