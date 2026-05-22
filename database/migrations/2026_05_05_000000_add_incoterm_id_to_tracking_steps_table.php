<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracking_steps', function (Blueprint $table) {
            $table->unsignedInteger('incoterm_id')->nullable();
        });

        Schema::table('tracking_steps', function (Blueprint $table) {
            $table->foreign('incoterm_id')
                ->references('id')
                ->on('incoterms')
                ->cascadeOnDelete();
        });

        $incoterms = DB::table('incoterms')
            ->join('tracking_steps', 'incoterms.tracking_steps_id', '=', 'tracking_steps.id')
            ->select([
                'incoterms.id as incoterm_id',
                'tracking_steps.nom',
                'tracking_steps.ordre',
            ])
            ->orderBy('incoterms.id')
            ->get();

        foreach ($incoterms as $incoterm) {
            $newTrackingStepId = DB::table('tracking_steps')->insertGetId([
                'nom' => $incoterm->nom,
                'ordre' => $incoterm->ordre,
                'incoterm_id' => $incoterm->incoterm_id,
            ]);

            DB::table('incoterms')
                ->where('id', $incoterm->incoterm_id)
                ->update(['tracking_steps_id' => $newTrackingStepId]);
        }
    }

    public function down(): void
    {
        Schema::table('tracking_steps', function (Blueprint $table) {
            $table->dropForeign(['incoterm_id']);
            $table->dropColumn('incoterm_id');
        });
    }
};