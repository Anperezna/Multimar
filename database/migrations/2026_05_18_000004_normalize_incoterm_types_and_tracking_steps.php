<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // 1) Unificar tipus_incoterms duplicados por codi+nom (ignorando mayúsculas y espacios)
            DB::statement("\n                ;WITH canonical AS (\n                    SELECT\n                        MIN(id) AS keep_id,\n                        UPPER(LTRIM(RTRIM(ISNULL(codi, '')))) AS codi_key,\n                        UPPER(LTRIM(RTRIM(ISNULL(nom, '')))) AS nom_key\n                    FROM tipus_incoterms\n                    GROUP BY\n                        UPPER(LTRIM(RTRIM(ISNULL(codi, '')))),\n                        UPPER(LTRIM(RTRIM(ISNULL(nom, ''))))\n                )\n                UPDATE i\n                SET i.tipus_inconterm_id = c.keep_id\n                FROM incoterms i\n                INNER JOIN tipus_incoterms t ON t.id = i.tipus_inconterm_id\n                INNER JOIN canonical c\n                    ON c.codi_key = UPPER(LTRIM(RTRIM(ISNULL(t.codi, ''))))\n                   AND c.nom_key = UPPER(LTRIM(RTRIM(ISNULL(t.nom, ''))))\n                WHERE i.tipus_inconterm_id <> c.keep_id\n            ");

            DB::statement("\n                ;WITH canonical AS (\n                    SELECT\n                        MIN(id) AS keep_id,\n                        UPPER(LTRIM(RTRIM(ISNULL(codi, '')))) AS codi_key,\n                        UPPER(LTRIM(RTRIM(ISNULL(nom, '')))) AS nom_key\n                    FROM tipus_incoterms\n                    GROUP BY\n                        UPPER(LTRIM(RTRIM(ISNULL(codi, '')))),\n                        UPPER(LTRIM(RTRIM(ISNULL(nom, ''))))\n                )\n                DELETE t\n                FROM tipus_incoterms t\n                INNER JOIN canonical c\n                    ON c.codi_key = UPPER(LTRIM(RTRIM(ISNULL(t.codi, ''))))\n                   AND c.nom_key = UPPER(LTRIM(RTRIM(ISNULL(t.nom, ''))))\n                WHERE t.id <> c.keep_id\n                  AND NOT EXISTS (\n                      SELECT 1\n                      FROM incoterms i\n                      WHERE i.tipus_inconterm_id = t.id\n                  )\n            ");

            // 2) Recuperar incoterm_id en pasos legacy enlazados como principal
            DB::statement("\n                UPDATE ts\n                SET ts.incoterm_id = i.id\n                FROM tracking_steps ts\n                INNER JOIN incoterms i ON i.tracking_steps_id = ts.id\n                WHERE ts.incoterm_id IS NULL\n            ");

            // 3) Borrar solo pasos huérfanos con incoterm_id NULL y sin historial
            DB::table('tracking_steps')
                ->whereNull('incoterm_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('tracking_history as th')
                        ->whereColumn('th.tracking_step_id', 'tracking_steps.id');
                })
                ->delete();

            // 4) Reasignar paso principal a un paso válido del propio incoterm
            DB::statement("\n                UPDATE i\n                SET i.tracking_steps_id = ts.id\n                FROM incoterms i\n                CROSS APPLY (\n                    SELECT TOP 1 id\n                    FROM tracking_steps\n                    WHERE incoterm_id = i.id\n                    ORDER BY ordre, id\n                ) ts\n            ");
        });
    }

    public function down(): void
    {
        // Migración de limpieza de datos: no reversible de forma segura.
    }
};
