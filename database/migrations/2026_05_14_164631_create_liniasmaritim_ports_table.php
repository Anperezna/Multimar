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
        Schema::create('liniasmaritim_ports', function (Blueprint $table) {
            $table->unsignedInteger('linia_transport_maritim_id');
            $table->unsignedInteger('port_id');
            $table->foreign('linia_transport_maritim_id')
                ->references('id')
                ->on('linies_transport_maritim')
                ->onDelete('cascade');
            $table->foreign('port_id')
                ->references('id')
                ->on('ports')
                ->onDelete('cascade');
            $table->primary(['linia_transport_maritim_id', 'port_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liniasmaritim_ports');
    }
};
