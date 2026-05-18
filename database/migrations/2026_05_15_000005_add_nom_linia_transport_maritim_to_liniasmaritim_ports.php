<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('liniasmaritim_ports', function (Blueprint $table) {
            $table->string('nom_linia_transport_maritim')->nullable()->after('port_id');
        });

        DB::statement(
            'UPDATE lp
                SET lp.nom_linia_transport_maritim = l.nom
             FROM liniasmaritim_ports lp
             INNER JOIN linies_transport_maritim l ON l.id = lp.linia_transport_maritim_id'
        );

        Schema::table('liniasmaritim_ports', function (Blueprint $table) {
            $table->string('nom_linia_transport_maritim')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liniasmaritim_ports', function (Blueprint $table) {
            $table->dropColumn('nom_linia_transport_maritim');
        });
    }
};