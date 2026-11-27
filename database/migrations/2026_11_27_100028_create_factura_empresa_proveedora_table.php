<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_empresa_proveedora', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('empresa_id')->nullable();
            $table->integer('docente_id')->nullable();
            $table->integer('curso_id')->nullable();
            $table->string('numero_factura', 30);
            $table->date('fecha');
            $table->date('fecha_registro');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('proveedor_cif', 15)->nullable();
            $table->string('proveedor_nombre', 100)->nullable();
            $table->decimal('base_imponible', 10, 2)->default(0.00);
            $table->decimal('total_iva', 10, 2)->default(0.00);
            $table->decimal('retencion_irpf', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->enum('forma_pago', ['TRANSFERENCIA', 'DOMICILIACION', 'TARJETA', 'EFECTIVO'])->default('TRANSFERENCIA');
            $table->enum('estado', ['PENDIENTE', 'PAGADA', 'ANULADA'])->default('PENDIENTE');
            $table->date('fecha_pago')->nullable();
            $table->string('documento', 255)->nullable();
            $table->integer('persona_id');
            $table->string('observaciones', 300)->nullable();
            $table->string('emisor_clave', 15)->storedAs('coalesce(concat(\'E\',empresa_id),concat(\'D\',docente_id))');

            $table->unique(['emisor_clave', 'numero_factura'], 'uq_fprov_emisor_numero');
            $table->index(['empresa_id'], 'fk_fprov_empresa_idx');
            $table->index(['docente_id'], 'fk_fprov_docente_idx');
            $table->index(['curso_id'], 'fk_fprov_curso_idx');
            $table->index(['persona_id'], 'fk_fprov_persona_idx');

            $table->foreign('curso_id', 'fk_fprov_curso')->references('id')->on('curso');
            $table->foreign('docente_id', 'fk_fprov_docente')->references('id')->on('persona');
            $table->foreign('empresa_id', 'fk_fprov_empresa')->references('id')->on('empresa');
            $table->foreign('persona_id', 'fk_fprov_persona')->references('id')->on('persona');
        });

        // CHECK: Schema no tiene método propio. SQLite (tests) no admite
        // añadir CHECK con ALTER TABLE, así que solo se crea en MySQL/MariaDB.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE factura_empresa_proveedora ADD CONSTRAINT chk_fprov_emisor CHECK (empresa_id is not null or docente_id is not null)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_empresa_proveedora');
    }
};
