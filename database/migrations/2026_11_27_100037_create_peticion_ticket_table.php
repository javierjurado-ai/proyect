<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peticion_ticket', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('peticiones_logistica_id');
            $table->string('titulo', 45);

            $table->index(['peticiones_logistica_id'], 'fk_peticion_peticiones_logistica1_idx');

            $table->foreign('peticiones_logistica_id', 'fk_peticion_peticiones_logistica1')->references('id')->on('peticiones_logistica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peticion_ticket');
    }
};
