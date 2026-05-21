<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Copiar datos relacionales de forma segura si la tabla antigua existe
        if (Schema::hasTable('tipus_tracking') && Schema::hasTable('incoterms')) {
            $registrosActuales = DB::table('tipus_tracking')->get();

            foreach ($registrosActuales as $registro) {
                // Evitamos duplicados en la tabla incoterms antes de insertar
                $existeRelacion = DB::table('incoterms')
                    ->where('tipus_inconterm_id', $registro->tipus_incoterm_id)
                    ->where('tracking_steps_id', $registro->tracking_step_id)
                    ->exists();

                if (!$existeRelacion) {
                    DB::table('incoterms')->insert([
                        'tipus_inconterm_id' => $registro->tipus_incoterm_id,
                        'tracking_steps_id'  => $registro->tracking_step_id,
                        // 🔥 Hemos eliminado las fechas aquí
                    ]);
                }
            }
        }

        // 2. Eliminar de forma definitiva la tabla redundante vieja
        Schema::dropIfExists('tipus_tracking');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('tipus_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipus_incoterm_id');
            $table->unsignedBigInteger('tracking_step_id');
            $table->timestamps();
        });
    }
};