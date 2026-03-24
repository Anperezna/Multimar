<?php

namespace Database\Seeders;

use App\Models\Ciutat;
use App\Models\Client;
use App\Models\EstatOferta;
use App\Models\Incoterm;
use App\Models\LiniaTransportMaritim;
use App\Models\Pais;
use App\Models\Port;
use App\Models\Rol;
use App\Models\Solicitud;
use App\Models\TipusCarrega;
use App\Models\TipusContenidor;
use App\Models\TipusFluxe;
use App\Models\TipusIncoterm;
use App\Models\TipusTransport;
use App\Models\TipusValidacio;
use App\Models\TrackingStep;
use App\Models\Transportista;
use App\Models\Usuari;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */


    public function run(): void
    {
        // User::factory(10)->create();
        $adminRol = Rol::firstOrCreate([
            'rol' => 'Admin',
        ]);

        Rol::firstOrCreate([
            'rol' => 'Operador',
        ]);

        Rol::firstOrCreate([
            'rol' => 'Client',
        ]);

        $admin = Usuari::updateOrCreate([
            'correu' => 'admin@multimar.com',
        ], [
            'contrasenya' => '123123',
            'nom' => 'Admin',
            'cognoms' => 'Admin',
            'rol_id' => $adminRol->id,
            'dni' => '65566565B',
        ]);

        $pais = Pais::firstOrCreate([
            'nom' => 'Espanya',
        ]);

        $ciutatOrigen = Ciutat::firstOrCreate([
            'nom' => 'Barcelona',
            'pais_id' => $pais->id,
        ]);

        $ciutatDesti = Ciutat::firstOrCreate([
            'nom' => 'Valencia',
            'pais_id' => $pais->id,
        ]);

        $portOrigen = Port::firstOrCreate([
            'nom' => 'Port Barcelona',
            'ciutat_id' => $ciutatOrigen->id,
        ]);

        $portDesti = Port::firstOrCreate([
            'nom' => 'Port Valencia',
            'ciutat_id' => $ciutatDesti->id,
        ]);

        $linia = LiniaTransportMaritim::firstOrCreate([
            'nom' => 'Linia Mediterrania',
            'ciutat_id' => $ciutatOrigen->id,
        ]);

        $transportista = Transportista::firstOrCreate([
            'nom' => 'Transmar Demo',
            'ciutat_id' => $ciutatOrigen->id,
        ]);

        $tipusTransport = TipusTransport::firstOrCreate([
            'tipus' => 'Maritim',
        ]);

        $tipusValidacio = TipusValidacio::firstOrCreate([
            'tipus' => 'Pendent',
        ]);

        $estatOferta = EstatOferta::firstOrCreate([
            'estat' => 'Nova',
        ]);

        $tipusIncoterm = TipusIncoterm::firstOrCreate([
            'codi' => 'FOB',
            'nom' => 'Free On Board',
        ]);

        $trackingStep = TrackingStep::firstOrCreate([
            'ordre' => 1,
            'nom' => 'Sortida',
        ]);

        $incoterm = Incoterm::firstOrCreate([
            'tipus_inconterm_id' => $tipusIncoterm->id,
            'tracking_steps_id' => $trackingStep->id,
        ]);

        $tipusContenidor = TipusContenidor::firstOrCreate([
            'tipus' => '20GP',
        ]);

        $tipusFluxe = TipusFluxe::firstOrCreate([
            'tipus' => 'Exportacio',
        ]);

        $tipusCarrega = TipusCarrega::firstOrCreate([
            'tipus' => 'General',
        ]);

        $client = Client::firstOrCreate([
            'foto_dni_front' => 'dni_front_demo.jpg',
            'foto_dni_back' => 'dni_back_demo.jpg',
        ], [
            'foto_user' => null,
        ]);

        $solicitud = Solicitud::where('mercancia_nombre', 'Mercancia demo')
            ->where('client_id', $client->id)
            ->where('operador_id', $admin->id)
            ->first();

        if (!$solicitud) {
            $solicitud = Solicitud::create([
                'id' => ((int) (Solicitud::max('id') ?? 0)) + 1,
                'mercancia_nombre' => 'Mercancia demo',
                'pes_brut' => 12000,
                'volum' => 28.5,
                'client_id' => $client->id,
                'operador_id' => $admin->id,
                'mercancia_tipus' => 1,
                'tipus_transport_id' => $tipusTransport->id,
                'tipus_contenidor_id' => $tipusContenidor->id,
                'origen_id' => $ciutatOrigen->id,
                'destino_id' => $ciutatDesti->id,
                'incoterm_id' => $incoterm->id,
                'tipus_fluxe_id' => $tipusFluxe->id,
                'tipus_carrega_id' => $tipusCarrega->id,
            ]);
        }

        $ofertaExistent = DB::table('ofertes')->where('solicitud_id', $solicitud->id)->first();

        if (!$ofertaExistent) {
            DB::table('ofertes')->insert([
                'tipus_transport_id' => $tipusTransport->id,
                'comentaris' => 'Oferta de prova creada des del seeder',
                'agent_comercial_id' => $admin->id,
                'transportista_origen_id' => $transportista->id,
                'tipus_validacio_id' => $tipusValidacio->id,
                'port_origen_id' => $portOrigen->id,
                'port_desti_id' => $portDesti->id,
                'linia_transport_maritim_id' => $linia->id,
                'estat_oferta_id' => $estatOferta->id,
                'data_creacio' => now()->toDateString(),
                'data_validessa_inicial' => now()->toDateString(),
                'data_validessa_fina' => now()->addDays(30)->toDateString(),
                'solicitud_id' => $solicitud->id,
                'documents_id' => null,
                'etd' => now()->addDays(7),
                'eta' => now()->addDays(20),
                'transportista_destino_id' => $transportista->id,
                'operador_id' => $admin->id,
                'acceptat' => 0,
                'vist' => 0,
                'acabat' => 0,
                'cancelat' => 0,
            ]);
        }
    }
}
