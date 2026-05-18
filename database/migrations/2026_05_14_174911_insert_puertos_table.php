<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar países primero (si no existen)
        DB::table('paissos')->insert([
            ['nom' => 'Espanya'],
            ['nom' => 'Singapur'],
            ['nom' => 'Paises Baixos'],
            ['nom' => 'Bélgica'],
            ['nom' => 'Hong Kong'],
            ['nom' => 'Estats Units'],
        ]);

        // Obtener IDs de países
        $paisEspanya = DB::table('paissos')->where('nom', 'Espanya')->value('id');
        $paisSingapur = DB::table('paissos')->where('nom', 'Singapur')->value('id');
        $paisPaisesBaixos = DB::table('paissos')->where('nom', 'Paises Baixos')->value('id');
        $paisBelgica = DB::table('paissos')->where('nom', 'Bélgica')->value('id');
        $paisHongKong = DB::table('paissos')->where('nom', 'Hong Kong')->value('id');
        $paisEstatsUnits = DB::table('paissos')->where('nom', 'Estats Units')->value('id');

        // Insertar ciudades con pais_id
        DB::table('ciutats')->insert([
            ['nom' => 'Barcelona', 'pais_id' => $paisEspanya],
            ['nom' => 'Valencia', 'pais_id' => $paisEspanya],
            ['nom' => 'Bilbao', 'pais_id' => $paisEspanya],
            ['nom' => 'Tarragona', 'pais_id' => $paisEspanya],
            ['nom' => 'Singapur', 'pais_id' => $paisSingapur],
            ['nom' => 'Rotterdam', 'pais_id' => $paisPaisesBaixos],
            ['nom' => 'Amberes', 'pais_id' => $paisBelgica],
            ['nom' => 'Hong Kong', 'pais_id' => $paisHongKong],
            ['nom' => 'Nueva York', 'pais_id' => $paisEstatsUnits],
            ['nom' => 'Miami', 'pais_id' => $paisEstatsUnits],
        ]);

        // Obtener IDs de ciudades
        $barcelona = DB::table('ciutats')->where('nom', 'Barcelona')->value('id');
        $valencia = DB::table('ciutats')->where('nom', 'Valencia')->value('id');
        $bilbao = DB::table('ciutats')->where('nom', 'Bilbao')->value('id');
        $tarragona = DB::table('ciutats')->where('nom', 'Tarragona')->value('id');
        $singapur = DB::table('ciutats')->where('nom', 'Singapur')->value('id');
        $rotterdam = DB::table('ciutats')->where('nom', 'Rotterdam')->value('id');
        $amberes = DB::table('ciutats')->where('nom', 'Amberes')->value('id');
        $hongKong = DB::table('ciutats')->where('nom', 'Hong Kong')->value('id');
        $nuevaYork = DB::table('ciutats')->where('nom', 'Nueva York')->value('id');
        $miami = DB::table('ciutats')->where('nom', 'Miami')->value('id');

        DB::table('ports')->insert([
            ['nom' => 'Puerto de Barcelona', 'ciutat_id' => $barcelona],
            ['nom' => 'Puerto de Valencia', 'ciutat_id' => $valencia],
            ['nom' => 'Puerto de Bilbao', 'ciutat_id' => $bilbao],
            ['nom' => 'Puerto de Tarragona', 'ciutat_id' => $tarragona],
            ['nom' => 'Puerto de Singapur', 'ciutat_id' => $singapur],
            ['nom' => 'Puerto de Rotterdam', 'ciutat_id' => $rotterdam],
            ['nom' => 'Puerto de Amberes', 'ciutat_id' => $amberes],
            ['nom' => 'Puerto de Hong Kong', 'ciutat_id' => $hongKong],
            ['nom' => 'Puerto de Nueva York', 'ciutat_id' => $nuevaYork],
            ['nom' => 'Puerto de Miami', 'ciutat_id' => $miami],
            ['nom' => 'Puerto de Róterdam Exterior', 'ciutat_id' => $rotterdam],
            ['nom' => 'Puerto de Santos', 'ciutat_id' => $barcelona],
            ['nom' => 'Puerto de Shangái', 'ciutat_id' => $hongKong],
            ['nom' => 'Puerto de Sídney', 'ciutat_id' => $valencia],
            ['nom' => 'Puerto de Los Ángeles', 'ciutat_id' => $nuevaYork],
            ['nom' => 'Puerto de Estambul', 'ciutat_id' => $bilbao],
            ['nom' => 'Puerto de Dubái', 'ciutat_id' => $singapur],
            ['nom' => 'Puerto de Róterdam Centro', 'ciutat_id' => $rotterdam],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ports')->whereIn('nom', [
            'Puerto de Barcelona',
            'Puerto de Valencia',
            'Puerto de Bilbao',
            'Puerto de Tarragona',
            'Puerto de Singapur',
            'Puerto de Rotterdam',
            'Puerto de Amberes',
            'Puerto de Hong Kong',
            'Puerto de Nueva York',
            'Puerto de Miami',
            'Puerto de Róterdam Exterior',
            'Puerto de Santos',
            'Puerto de Shangái',
            'Puerto de Sídney',
            'Puerto de Los Ángeles',
            'Puerto de Estambul',
            'Puerto de Dubái',
            'Puerto de Róterdam Centro',
        ])->delete();
        DB::table('ciutats')->whereIn('nom', [
            'Barcelona',
            'Valencia',
            'Bilbao',
            'Tarragona',
            'Singapur',
            'Rotterdam',
            'Amberes',
            'Hong Kong',
            'Nueva York',
            'Miami',
        ])->delete();
        DB::table('paissos')->whereIn('nom', [
            'Espanya',
            'Singapur',
            'Paises Baixos',
            'Bélgica',
            'Hong Kong',
            'Estats Units',
        ])->delete();
    }
};
