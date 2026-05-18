<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('linies_transport_maritim', function (Blueprint $table) {
            if (!Schema::hasColumn('linies_transport_maritim', 'nom')) {
                $table->string('nom')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('linies_transport_maritim', function (Blueprint $table) {
            if (Schema::hasColumn('linies_transport_maritim', 'nom')) {
                $table->dropColumn('nom');
            }
        });
    }
};
