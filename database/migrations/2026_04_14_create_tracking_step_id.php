<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ofertes', function (Blueprint $table) {
            $table->unsignedInteger('tracking_step_id')->nullable()->after('cancelat');
        });

        Schema::table('solicitud', function (Blueprint $table) {
            $table->unsignedInteger('tracking_step_id')->nullable()->after('tipus_carrega_id');
        });
    }

    public function down(): void
    {
        Schema::table('ofertes', function (Blueprint $table) {
            $table->dropColumn('tracking_step_id');
        });

        Schema::table('solicitud', function (Blueprint $table) {
            $table->dropColumn('tracking_step_id');
        });
    }
};
