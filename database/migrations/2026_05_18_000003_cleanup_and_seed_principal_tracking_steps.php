<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // 1) Recuperar relación directa desde incoterms.tracking_steps_id
            DB::statement("
                UPDATE ts
                SET ts.incoterm_id = i.id
                FROM tracking_steps ts
                INNER JOIN incoterms i ON i.tracking_steps_id = ts.id
                WHERE ts.incoterm_id IS NULL
            ");

            // 2) Eliminar solo pasos huérfanos sin incoterm y sin uso en tracking_history
            DB::table('tracking_steps')
                ->whereNull('incoterm_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('tracking_history as th')
                        ->whereColumn('th.tracking_step_id', 'tracking_steps.id');
                })
                ->delete();

            // 3) Reasignar el paso principal del incoterm al primer paso válido
            DB::statement("\n                UPDATE i\n                SET i.tracking_steps_id = ts.id\n                FROM incoterms i\n                CROSS APPLY (\n                    SELECT TOP 1 id\n                    FROM tracking_steps\n                    WHERE incoterm_id = i.id\n                    ORDER BY ordre, id\n                ) ts\n            ");
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            DB::statement("\n                UPDATE i\n                SET i.tracking_steps_id = ts.id\n                FROM incoterms i\n                CROSS APPLY (\n                    SELECT TOP 1 id\n                    FROM tracking_steps\n                    WHERE incoterm_id = i.id\n                    ORDER BY ordre, id\n                ) ts\n            ");
        });
    }
};
