<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::statement("\n                INSERT INTO tracking_steps (ordre, nom, incoterm_id, activo)\n                SELECT 1, 'Principal', i.id, 1\n                FROM incoterms i\n                WHERE NOT EXISTS (\n                    SELECT 1\n                    FROM tracking_steps ts\n                    WHERE ts.incoterm_id = i.id\n                      AND ts.nom = 'Principal'\n                )\n            ");

            DB::statement("\n                UPDATE i\n                SET i.tracking_steps_id = ts.id\n                FROM incoterms i\n                INNER JOIN tracking_steps ts\n                    ON ts.incoterm_id = i.id\n                   AND ts.nom = 'Principal'\n            ");
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            DB::statement("\n                UPDATE i\n                SET i.tracking_steps_id = NULL\n                FROM incoterms i\n                INNER JOIN tracking_steps ts ON ts.id = i.tracking_steps_id\n                WHERE ts.nom = 'Principal'\n            ");

            DB::table('tracking_steps')
                ->where('nom', 'Principal')
                ->delete();
        });
    }
};
