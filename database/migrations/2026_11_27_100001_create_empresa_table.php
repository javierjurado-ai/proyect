<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('cif', 15);
            $table->string('nombre', 100);
            $table->string('direccion', 150)->nullable();
            $table->char('codigo_postal', 5)->nullable();
            $table->string('municipio', 45)->nullable();
            $table->string('provincia', 45)->nullable();
            $table->string('email_facturacion', 100)->nullable();
            $table->smallInteger('dias_vencimiento')->default(30);
            $table->string('telefono', 20)->nullable();
            $table->integer('total_trabajadores')->nullable();
            $table->boolean('activo')->default(true);

            $table->unique(['cif'], 'uq_empresa_cif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
