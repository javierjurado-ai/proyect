<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peticiones_logistica', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('curso_id')->nullable();
            $table->integer('solicitante_id')->nullable();
            $table->integer('almacen_id')->nullable();
            $table->integer('almacen_destino_id')->nullable();
            $table->integer('centro_id')->nullable();
            $table->date('fecha');
            $table->date('fecha_necesaria')->nullable();
            $table->date('fecha_devolucion_prevista')->nullable();
            $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'ENVIADA', 'ENTREGADA', 'DEVUELTA', 'COMPLETADA', 'CANCELADA']);
            $table->string('observaciones', 200)->nullable();
            $table->string('direccion_envio', 150)->nullable();
            $table->string('transportista', 45)->nullable();
            $table->string('seguimiento', 45)->nullable();
            $table->decimal('coste_envio', 10, 2)->default(0.00);

            $table->index(['curso_id'], 'fk_pet_log_curso_idx');
            $table->index(['solicitante_id'], 'fk_pet_log_persona_idx');
            $table->index(['almacen_id'], 'fk_pet_log_almacen_idx');
            $table->index(['centro_id'], 'fk_pet_log_centro_idx');
            $table->index(['almacen_destino_id'], 'fk_pet_log_almacen_destino');

            $table->foreign('almacen_id', 'fk_pet_log_almacen')->references('id')->on('almacen');
            $table->foreign('almacen_destino_id', 'fk_pet_log_almacen_destino')->references('id')->on('almacen');
            $table->foreign('centro_id', 'fk_pet_log_centro')->references('id')->on('centro');
            $table->foreign('curso_id', 'fk_pet_log_curso')->references('id')->on('curso');
            $table->foreign('solicitante_id', 'fk_pet_log_persona')->references('id')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peticiones_logistica');
    }
};
