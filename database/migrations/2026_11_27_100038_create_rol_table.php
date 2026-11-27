<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('rol', ['ADMINISTRADOR', 'COMERCIAL', 'COORDINADOR', 'LOGISTA', 'DOCENTE', 'ALUMNO'])->nullable();
            $table->enum('rol_empresa', ['FORMADORA', 'CLIENTEL', 'PROVEEDORA', 'CLIENTE', 'PROVEEDOR'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol');
    }
};
