<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipus_tracking', function (Blueprint $table) {
            if (! Schema::hasColumn('tipus_tracking', 'tipus_incoterm_id')) {
                $table->unsignedInteger('tipus_incoterm_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('tipus_tracking', 'tracking_step_id')) {
                $table->unsignedInteger('tracking_step_id')->nullable()->after('tipus_incoterm_id');
            }
        });

        DB::statement("
            UPDATE tt
            SET
                tt.tipus_incoterm_id = ti.id,
                tt.tracking_step_id = tt.tracking_steps_id
            FROM tipus_tracking tt
            INNER JOIN tipus_incoterms ti ON ti.nom = tt.tipus_nom
        ");

        DB::statement("
            ;WITH duplicated AS (
                SELECT
                    id,
                    ROW_NUMBER() OVER (
                        PARTITION BY tipus_incoterm_id, tracking_step_id
                        ORDER BY id
                    ) AS rn
                FROM tipus_tracking
                WHERE tipus_incoterm_id IS NOT NULL
                  AND tracking_step_id IS NOT NULL
            )
            DELETE FROM duplicated
            WHERE rn > 1
        ");

        Schema::table('tipus_tracking', function (Blueprint $table) {
            if (Schema::hasColumn('tipus_tracking', 'tipus_nom')) {
                $table->dropColumn('tipus_nom');
            }

            if (Schema::hasColumn('tipus_tracking', 'tracking_steps_id')) {
                $table->dropColumn('tracking_steps_id');
            }
        });

        Schema::table('tipus_tracking', function (Blueprint $table) {
            $table->unique(['tipus_incoterm_id', 'tracking_step_id'], 'uq_tipus_tracking_tipo_step');
        });

        Schema::table('tipus_tracking', function (Blueprint $table) {
            $table->foreign('tipus_incoterm_id', 'fk_tipus_tracking_tipus_incoterm')
                ->references('id')
                ->on('tipus_incoterms')
                ->cascadeOnDelete();

            $table->foreign('tracking_step_id', 'fk_tipus_tracking_tracking_step')
                ->references('id')
                ->on('tracking_steps')
                ->cascadeOnDelete();
        });

        Schema::dropIfExists('incoterm_tracking_steps');
    }

    public function down(): void
    {
        Schema::create('incoterm_tracking_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('incoterm_id')->nullable();
            $table->unsignedInteger('tracking_step_id')->nullable();
            $table->integer('ordre')->nullable();
            $table->string('nom')->nullable();
        });
    }
};
