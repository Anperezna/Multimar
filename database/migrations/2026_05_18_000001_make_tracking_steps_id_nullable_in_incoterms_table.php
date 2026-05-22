<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE incoterms DROP CONSTRAINT FK_incoterms_tracking_steps');
        DB::statement('ALTER TABLE incoterms ALTER COLUMN tracking_steps_id INT NULL');
        DB::statement('ALTER TABLE incoterms ADD CONSTRAINT FK_incoterms_tracking_steps FOREIGN KEY (tracking_steps_id) REFERENCES tracking_steps (id)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE incoterms DROP CONSTRAINT FK_incoterms_tracking_steps');
        DB::statement('ALTER TABLE incoterms ALTER COLUMN tracking_steps_id INT NOT NULL');
        DB::statement('ALTER TABLE incoterms ADD CONSTRAINT FK_incoterms_tracking_steps FOREIGN KEY (tracking_steps_id) REFERENCES tracking_steps (id)');
    }
};