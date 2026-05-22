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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRolId = null;
        $operadorRolId = null;

        // 1. Control de Roles
        if (Schema::hasTable('rols')) {
            $adminRol = Rol::firstOrCreate(['rol' => 'Admin']);
            $operadorRol = Rol::firstOrCreate(['rol' => 'Operador']);
            Rol::firstOrCreate(['rol' => 'Usuari']);
            
            $adminRolId = $adminRol->id;
            $operadorRolId = $operadorRol->id;
        }

        // 2. Control de Usuarios
        $adminId = null;
        if (Schema::hasTable('usuaris')) {
            $admin = Usuari::updateOrCreate([
                'correu' => 'admin@multimar.com',
            ], [
                'contrasenya' => Hash::make('123123'),
                'nom' => 'Admin',
                'cognoms' => 'Admin',
                'rol_id' => $adminRolId,
                'dni' => '65566565B',
            ]);
            $adminId = $admin->id;

            Usuari::updateOrCreate([
                'correu' => 'operador@multimar.com',
            ], [
                'contrasenya' => Hash::make('123123'),
                'nom' => 'Operador',
                'cognoms' => 'Operador',
                'rol_id' => $operadorRolId,
                'dni' => '65566565B',
            ]);
        }

        // 3. Control de Geografía y Puertos
        $ciutatOrigenId = null;
        $ciutatDestiId = null;
        $portOrigenId = null;
        $portDestiId = null;

        if (Schema::hasTable('pais')) {
            $pais = Pais::firstOrCreate(['nom' => 'Espanya']);

            if (Schema::hasTable('ciutats')) {
                $ciutatOrigen = Ciutat::firstOrCreate(['nom' => 'Barcelona', 'pais_id' => $pais->id]);
                $ciutatDesti = Ciutat::firstOrCreate(['nom' => 'Valencia', 'pais_id' => $pais->id]);
                $ciutatOrigenId = $ciutatOrigen->id;
                $ciutatDestiId = $ciutatDesti->id;

                if (Schema::hasTable('ports')) {
                    $portOrigen = Port::firstOrCreate(['nom' => 'Port Barcelona', 'ciutat_id' => $ciutatOrigen->id]);
                    $portDesti = Port::firstOrCreate(['nom' => 'Port Valencia', 'ciutat_id' => $ciutatDesti->id]);
                    $portOrigenId = $portOrigen->id;
                    $portDestiId = $portDesti->id;
                }
            }
        }

        // 4. Líneas y Transportistas
        $liniaId = null;
        $transportistaId = null;
        if (Schema::hasTable('linia_transport_maritims') && $ciutatOrigenId) {
            $linia = LiniaTransportMaritim::firstOrCreate(['nom' => 'Linia Mediterrania', 'ciutat_id' => $ciutatOrigenId]);
            $liniaId = $linia->id;
        }
        if (Schema::hasTable('transportistas') && $ciutatOrigenId) {
            $transportista = Transportista::firstOrCreate(['nom' => 'Transmar Demo', 'ciutat_id' => $ciutatOrigenId]);
            $transportistaId = $transportista->id;
        }

        // 5. Tipos y Parámetros base
        $tipusTransportId = null;
        $tipusValidacioId = null;
        $estatOfertaId = null;
        $incotermId = null;
        $tipusContenidorId = null;
        $tipusFluxeId = null;
        $tipusCarregaId = null;

        if (Schema::hasTable('tipus_transports')) {
            $tipusTransport = TipusTransport::firstOrCreate(['tipus' => 'Maritim']);
            $tipusTransportId = $tipusTransport->id;
        }
        if (Schema::hasTable('tipus_validacios')) {
            $tipusValidacio = TipusValidacio::firstOrCreate(['tipus' => 'Pendent']);
            $tipusValidacioId = $tipusValidacio->id;
        }
        if (Schema::hasTable('estat_ofertas')) {
            $estatOferta = EstatOferta::firstOrCreate(['estat' => 'Nova']);
            $estatOfertaId = $estatOferta->id;
        }
        if (Schema::hasTable('tipus_incoterms') && Schema::hasTable('tracking_steps') && Schema::hasTable('incoterms')) {
            $tipusIncoterm = TipusIncoterm::firstOrCreate(['codi' => 'FOB', 'nom' => 'Free On Board']);
            $trackingStep = TrackingStep::firstOrCreate(['ordre' => 1, 'nom' => 'Sortida']);
            $incoterm = Incoterm::firstOrCreate(['tipus_inconterm_id' => $tipusIncoterm->id, 'tracking_steps_id' => $trackingStep->id]);
            $incotermId = $incoterm->id;
        }
        if (Schema::hasTable('tipus_contenidors')) {
            $tipusContenidor = TipusContenidor::firstOrCreate(['tipus' => '20GP']);
            $tipusContenidorId = $tipusContenidor->id;
        }
        if (Schema::hasTable('tipus_fluxes')) {
            $tipusFluxe = TipusFluxe::firstOrCreate(['tipus' => 'Exportacio']);
            $tipusFluxeId = $tipusFluxe->id;
        }
        if (Schema::hasTable('tipus_carregas')) {
            $tipusCarrega = TipusCarrega::firstOrCreate(['tipus' => 'General']);
            $tipusCarregaId = $tipusCarrega->id;
        }

        // 6. Clientes, Solicitudes y Ofertas
        if (Schema::hasTable('clients')) {
            $client = Client::firstOrCreate([
                'foto_dni_front' => 'dni_front_demo.jpg',
                'foto_dni_back' => 'dni_back_demo.jpg',
            ], [
                'foto_user' => null,
            ]);

            if (Schema::hasTable('solicituds')) {
                $solicitud = Solicitud::where('mercancia_nombre', 'Mercancia demo')
                    ->where('client_id', $client->id)
                    ->where('operador_id', $adminId)
                    ->first();

                if (!$solicitud) {
                    $solicitud = Solicitud::create([
                        'id' => ((int) (Solicitud::max('id') ?? 0)) + 1,
                        'mercancia_nombre' => 'Mercancia demo',
                        'pes_brut' => 12000,
                        'volum' => 28.5,
                        'client_id' => $client->id,
                        'operador_id' => $adminId,
                        'mercancia_tipus' => 1,
                        'tipus_transport_id' => $tipusTransportId,
                        'tipus_contenidor_id' => $tipusContenidorId,
                        'origen_id' => $ciutatOrigenId,
                        'destino_id' => $ciutatDestiId,
                        'incoterm_id' => $incotermId,
                        'tipus_fluxe_id' => $tipusFluxeId,
                        'tipus_carrega_id' => $tipusCarregaId,
                    ]);
                }

                if (Schema::hasTable('ofertes')) {
                    $ofertaExistent = DB::table('ofertes')->where('solicitud_id', $solicitud->id)->first();

                    if (!$ofertaExistent) {
                        DB::table('ofertes')->insert([
                            'tipus_transport_id' => $tipusTransportId,
                            'comentaris' => 'Oferta de prova creada des del seeder',
                            'agent_comercial_id' => $adminId,
                            'transportista_origen_id' => $transportistaId,
                            'tipus_validacio_id' => $tipusValidacioId,
                            'port_origen_id' => $portOrigenId,
                            'port_desti_id' => $portDestiId,
                            'linia_transport_maritim_id' => $liniaId,
                            'estat_oferta_id' => $estatOfertaId,
                            'data_creacio' => now()->toDateString(),
                            'data_validessa_inicial' => now()->toDateString(),
                            'data_validessa_fina' => now()->addDays(30)->toDateString(),
                            'solicitud_id' => $solicitud->id,
                            'documents_id' => null,
                            'etd' => now()->addDays(7),
                            'eta' => now()->addDays(20),
                            'transportista_destino_id' => $transportistaId,
                            'operador_id' => $adminId,
                            'acceptat' => 0,
                            'vist' => 0,
                            'acabat' => 0,
                            'cancelat' => 0,
                        ]);
                    }
                }
            }
        }
    }
}