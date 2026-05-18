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
        // Modificar la restricción de clave foránea en la tabla ofertes usando SQL puro
        DB::statement("
            ALTER TABLE ofertes 
            DROP CONSTRAINT FK_ofertes_linies_transport_maritim
        ");
        
        DB::statement("
            ALTER TABLE ofertes
            ADD CONSTRAINT FK_ofertes_linies_transport_maritim
            FOREIGN KEY (linia_transport_maritim_id)
            REFERENCES linies_transport_maritim(id)
            ON DELETE CASCADE
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE ofertes 
            DROP CONSTRAINT FK_ofertes_linies_transport_maritim
        ");
        
        DB::statement("
            ALTER TABLE ofertes
            ADD CONSTRAINT FK_ofertes_linies_transport_maritim
            FOREIGN KEY (linia_transport_maritim_id)
            REFERENCES linies_transport_maritim(id)
        ");
    }
};
