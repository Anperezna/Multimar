<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            $table->index('client_id', 'solicitud_client_id_index');
            $table->index('operador_id', 'solicitud_operador_id_index');
        });

        Schema::table('ofertes', function (Blueprint $table) {
            $table->index('operador_id', 'ofertes_operador_id_index');
            $table->index('solicitud_id', 'ofertes_solicitud_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('ofertes', function (Blueprint $table) {
            $table->dropIndex('ofertes_operador_id_index');
            $table->dropIndex('ofertes_solicitud_id_index');
        });

        Schema::table('solicitud', function (Blueprint $table) {
            $table->dropIndex('solicitud_client_id_index');
            $table->dropIndex('solicitud_operador_id_index');
        });
    }
};
