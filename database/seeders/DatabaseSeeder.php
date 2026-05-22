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

        if (Schema::hasTable('paissos')) {
            $pais = Pais::firstOrCreate(['nom' => 'Espanya']);

            if (Schema::hasTable('ciutats')) {
                $ciutatOrigen = Ciutat::firstOrCreate(['nom' => 'Barcelona', 'pais_id' => $pais->id]);
                $ciutatDesti = Ciutat::firstOrCreate(['nom' => 'Valencia', 'pais_id' => $pais->id]);
                $ciutatOrigenId = $ciutatOrigen->id;
                $ciutatDestiId = $ciutatDesti->id;

                if (Schema::hasTable('ports')) {
                    $portOrigen = Port::firstOrCreate(['nom' => 'Port Barcelona', 'ciutat_id' => $ciutatOrigenId]);
                    $portDesti = Port::firstOrCreate(['nom' => 'Port Valencia', 'ciutat_id' => $ciutatDestiId]);
                    $portOrigenId = $portOrigen->id;
                    $portDestiId = $portDesti->id;
                }
            }
        }

        // 4. Líneas y Transportistas
        $liniaId = null;
        $transportistaId = null;
        if (Schema::hasTable('linies_transport_maritim') && $ciutatOrigenId) {
            $linia = LiniaTransportMaritim::firstOrCreate(['nom' => 'Linia Mediterrania', 'ciutat_id' => $ciutatOrigenId]);
            $liniaId = $linia->id;
        }
        if (Schema::hasTable('transportistes') && $ciutatOrigenId) {
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
        if (Schema::hasTable('tipus_validacions')) {
            $tipusValidacio = TipusValidacio::firstOrCreate(['tipus' => 'Pendent']);
            $tipusValidacioId = $tipusValidacio->id;
        }
        if (Schema::hasTable('estats_ofertes')) {
            $estatOferta = EstatOferta::firstOrCreate(['estat' => 'Nova']);
            $estatOfertaId = $estatOferta->id;
        }
        if (Schema::hasTable('tipus_contenidors')) {
            $tipusContenidor = TipusContenidor::firstOrCreate(['tipus' => '20GP']);
            $tipusContenidorId = $tipusContenidor->id;
        }
        if (Schema::hasTable('tipus_fluxes')) {
            $tipusFluxe = TipusFluxe::firstOrCreate(['tipus' => 'Exportacio']);
            $tipusFluxeId = $tipusFluxe->id;
        }

        // --- NUEVA INYECCIÓN MASIVA DE DATOS ---

        // Tipos de Carga
        if (Schema::hasTable('tipus_carrega')) {
            $carregues = [
                'LCL - Grupatge', 'FCL - Contenidor Complet', 'Càrrega General / Fraccionada',
                'Càrrega Aèria Standard', 'Càrrega Perillosa (ADR/IMO)', 'Càrrega Refrigerada'
            ];
            foreach ($carregues as $carrega) {
                $tipus = TipusCarrega::firstOrCreate(['tipus' => $carrega]);
                if ($carrega === 'Càrrega General / Fraccionada') {
                    $tipusCarregaId = $tipus->id;
                }
            }
        }

        // Tracking Steps & Incoterms (con relaciones)
        if (Schema::hasTable('tipus_incoterms') && Schema::hasTable('tracking_steps') && Schema::hasTable('incoterms')) {
            
            // 1. Crear Tracking Steps
            $steps = [
                1 => 'Recollida en origen', 2 => 'Arribada a magatzem de consolidació',
                3 => 'Despatx de duana d\'exportació', 4 => 'Sortida del port/aeroport d\'origen',
                5 => 'En trànsit internacional', 6 => 'Arribada al port/aeroport de destí',
                7 => 'Despatx de duana d\'importació', 8 => 'En repartiment (Last mile)',
                9 => 'Lliurat al client final'
            ];
            foreach ($steps as $ordre => $nom) {
                TrackingStep::firstOrCreate(['ordre' => $ordre], ['nom' => $nom]);
            }

            // 2. Crear Tipos de Incoterms
            $incoterms = [
                'EXW' => 'Ex Works', 'FCA' => 'Free Carrier', 'CPT' => 'Carriage Paid To',
                'CIP' => 'Carriage and Insurance Paid To', 'DAP' => 'Delivered at Place',
                'DPU' => 'Delivered at Place Unloaded', 'DDP' => 'Delivered Duty Paid',
                'FAS' => 'Free Alongside Ship', 'FOB' => 'Free on Board',
                'CFR' => 'Cost and Freight', 'CIF' => 'Cost, Insurance and Freight'
            ];
            foreach ($incoterms as $codi => $nom) {
                TipusIncoterm::firstOrCreate(['codi' => $codi], ['nom' => $nom]);
            }

            // 3. Crear las Relaciones (Incoterm -> Tracking Step)
            $mappings = [
                'EXW' => 1, 'FCA' => 2, 'CPT' => 4, 'CIP' => 4, 'DAP' => 8,
                'DPU' => 9, 'DDP' => 9, 'FAS' => 4, 'FOB' => 4, 'CFR' => 6, 'CIF' => 6
            ];

            foreach ($mappings as $codi => $stepOrdre) {
                $tIncoterm = TipusIncoterm::where('codi', $codi)->first();
                $tStep = TrackingStep::where('ordre', $stepOrdre)->first();
                
                if ($tIncoterm && $tStep) {
                    $relacio = Incoterm::firstOrCreate([
                        'tipus_inconterm_id' => $tIncoterm->id,
                        'tracking_steps_id' => $tStep->id
                    ]);
                    
                    // Guardamos el ID del FOB para usarlo luego en la creación de la Solicitud
                    if ($codi === 'FOB') {
                        $incotermId = $relacio->id;
                    }
                }
            }
        }

        // 6. Clientes, Solicitudes y Ofertas
        if (Schema::hasTable('clients')) {
            $client = Client::firstOrCreate([
                'foto_dni_front' => 'dni_front_demo.jpg',
                'foto_dni_back' => 'dni_back_demo.jpg',
            ], [
                'foto_user' => null,
            ]);

            if (Schema::hasTable('solicitud')) { // Cambiado de 'solicituds' a 'solicitud' según tu modelo
                $solicitud = Solicitud::where('mercancia_nombre', 'Mercancia demo')
                    ->where('client_id', $client->id)
                    ->where('operador_id', $adminId)
                    ->first();

                if (!$solicitud) {
                    $solicitud = Solicitud::create([
                        // Borramos la línea del ID manual
                        'mercancia_nombre' => 'Mercancia demo',
                        'pes_brut' => 12000,
                        'volum' => 28.5,
                        'client_id' => $client->id,
                        'operador_id' => $adminId,
                        'mercancia_tipus' => '1',
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