<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peticion_mensajes', function (Blueprint $table) {
            // Mismo tipo que peticion_ticket.id: integer con signo
            $table->integer('peticion_ticket_id')->after('persona_id');

            $table->index(['peticion_ticket_id'], 'fk_peticion_mensajes_peticion_ticket1_idx');

            $table->foreign('peticion_ticket_id', 'fk_peticion_mensajes_peticion_ticket1')
                ->references('id')->on('peticion_ticket')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('peticion_mensajes', function (Blueprint $table) {
            $table->dropForeign('fk_peticion_mensajes_peticion_ticket1');
            $table->dropIndex('fk_peticion_mensajes_peticion_ticket1_idx');
            $table->dropColumn('peticion_ticket_id');
        });
    }
};
