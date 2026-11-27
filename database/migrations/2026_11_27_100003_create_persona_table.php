<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre', 45);
            $table->string('primer_apellido', 45);
            $table->string('segundo_apellido', 45)->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['MUJER', 'HOMBRE']);
            $table->string('nacionalidad', 45);
            $table->string('telefono', 20);
            $table->string('direccion', 150);
            $table->string('email', 100);
            $table->string('DNI', 15)->nullable();
            $table->string('NIE', 15)->nullable();
            $table->string('numero_seguridad_social', 20);
            $table->enum('LOPD', ['SI', 'NO']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
