<?php

namespace App\Http\Controllers;

use App\Models\EstatOferta;
use App\Models\LiniaTransportMaritim;
use App\Models\Notificacion;
use App\Models\Oferta;
use App\Models\Port;
use App\Models\Solicitud;
use App\Models\TipusValidacio;
use App\Models\Transportista;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    private function solicitudQuery()
    {
        return Solicitud::query()
            ->leftJoin('tipus_transports as tt', 'solicitud.tipus_transport_id', '=', 'tt.id')
            ->leftJoin('tipus_fluxes as tf', 'solicitud.tipus_fluxe_id', '=', 'tf.id')
            ->leftJoin('ciutats as co', 'solicitud.origen_id', '=', 'co.id')
            ->leftJoin('ciutats as cd', 'solicitud.destino_id', '=', 'cd.id')
            ->leftJoin('usuaris as u', 'solicitud.operador_id', '=', 'u.id')
            ->leftJoin('tipus_contenidors as tcon', 'solicitud.tipus_contenidor_id', '=', 'tcon.id')
            ->leftJoin('tipus_carrega as tcar', 'solicitud.tipus_carrega_id', '=', 'tcar.id')
            ->leftJoin('incoterms as i', 'solicitud.incoterm_id', '=', 'i.id')
            ->leftJoin('tipus_incoterms as ti', 'i.tipus_inconterm_id', '=', 'ti.id')
            ->select([
                'solicitud.id',
                'solicitud.mercancia_nombre',
                'solicitud.pes_brut',
                'solicitud.volum',
                'solicitud.client_id',
                'solicitud.operador_id',
                'solicitud.tipus_transport_id',
                'tt.tipus as tipus_transport_nom',
                'tf.tipus as operacio_nom',
                'co.nom as origen_nom',
                'cd.nom as destino_nom',
                'u.nom as operador_nom',
                'u.cognoms as operador_cognoms',
                'tcon.tipus as tipus_contenidor_nom',
                'tcar.tipus as tipus_carrega_nom',
                'i.id as incoterm_id',
                'ti.codi as incoterm_codi',
                'ti.nom as incoterm_nom',
            ]);
    }

    private function toFrontendSolicitud($solicitud)
    {
        $id = (int) $solicitud->id;

        return [
            'id' => $id,
            'code' => (string) $id,
            'kind' => 'Solicitud',
            'description' => trim((string) ($solicitud->mercancia_nombre ?? '')),
            'operation' => trim((string) ($solicitud->operacio_nom ?? '')),
            'status' => 'Pendiente',
            'carrier' => '-',
            'lastUpdate' => '-',
            'mercanciaNombre' => trim((string) ($solicitud->mercancia_nombre ?? '')),
            'pesoBrut' => $solicitud->pes_brut,
            'volum' => $solicitud->volum,
            'tipusTransportNom' => trim((string) ($solicitud->tipus_transport_nom ?? '')),
            'tipusContenidorNom' => trim((string) ($solicitud->tipus_contenidor_nom ?? '')),
            'tipusCarregaNom' => trim((string) ($solicitud->tipus_carrega_nom ?? '')),
            'origenNom' => trim((string) ($solicitud->origen_nom ?? '')),
            'destinoNom' => trim((string) ($solicitud->destino_nom ?? '')),
            'operadorNom' => trim((string) (($solicitud->operador_nom ?? '') . ' ' . ($solicitud->operador_cognoms ?? ''))),
            'incotermText' => trim((string) (($solicitud->incoterm_codi ?? '') . ' ' . ($solicitud->incoterm_nom ?? ''))),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $solicitudes = $this->solicitudQuery()
            ->leftJoin('ofertes as o', 'solicitud.id', '=', 'o.solicitud_id')
            ->whereNull('o.id')
            ->orderByDesc('solicitud.id')
            ->get()
            ->map(fn ($solicitud) => $this->toFrontendSolicitud($solicitud))
            ->values();

        return response()->json($solicitudes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'origen_id' => ['required', 'integer', 'exists:ciutats,id'],
            'destino_id' => ['required', 'integer', 'exists:ciutats,id'],
            'incoterm_id' => ['required', 'integer', 'exists:incoterms,id'],
        ]);

        $crearSolicitud = new Solicitud;
        $crearSolicitud->id = (int) (Solicitud::max('id') ?? 0) + 1;
        $crearSolicitud->mercancia_nombre = $request->input('nombreMercancia', 'Sin especificar');
        $crearSolicitud->pes_brut = (float) $request->input('pesoBruto', 0);
        $crearSolicitud->volum = (float) $request->input('volumen', 0);
        $crearSolicitud->client_id = $request->user()->id;
        $crearSolicitud->operador_id = $request->integer('operador', 1);
        $crearSolicitud->mercancia_tipus = $request->integer('tipoMercancia', 1);
        $crearSolicitud->tipus_transport_id = $request->integer('tipus_transport_id', 1);
        $crearSolicitud->tipus_contenidor_id = $request->integer('tipoContenedor', 1);
        $crearSolicitud->origen_id = $request->integer('origen_id', 1);
        $crearSolicitud->destino_id = $request->integer('destino_id', 1);
        $crearSolicitud->incoterm_id = $request->integer('incoterm_id', 1);
        $crearSolicitud->tipus_fluxe_id = $request->integer('tipus_fluxe_id', 1);
        $crearSolicitud->tipus_carrega_id = $request->integer('tipoCarrega', 1);
        
        $crearSolicitud->save();

        $notificacion = new Notificacion();
        $notificacion->id = (int) (Notificacion::max('id') ?? 0) + 1;
        $notificacion->usuari_id = $crearSolicitud->operador_id;
        $notificacion->titulo = 'Nueva Solicitud de Pedido';
        $notificacion->descripcion = 'Se ha creado una solicitud: ' . $crearSolicitud->mercancia_nombre;
        $notificacion->visto = 0;
        $notificacion->save();

        return response()->json(['message' => 'Solicitud creada exitosamente'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Solicitud $solicitud)
    {
        $solicitudDetalle = $this->solicitudQuery()
            ->where('solicitud.id', $solicitud->id)
            ->first();

        if (!$solicitudDetalle) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }

        return response()->json($this->toFrontendSolicitud($solicitudDetalle));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Solicitud $solicitud)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Solicitud $solicitud)
    {
        $solicitud->delete();

        return response()->noContent();
    }

    public function accept(Solicitud $solicitud)
    {
        $ofertaExistente = Oferta::query()->where('solicitud_id', $solicitud->id)->first();

        if ($ofertaExistente) {
            $solicitud->delete();

            return response()->json([
                'message' => 'La solicitud ya estaba convertida en oferta',
                'oferta_id' => $ofertaExistente->id,
            ]);
        }

        $transportista = Transportista::query()->first();
        $portOrigen = Port::query()->where('ciutat_id', $solicitud->origen_id)->first() ?? Port::query()->first();
        $portDesti = Port::query()->where('ciutat_id', $solicitud->destino_id)->first() ?? Port::query()->first();
        $linia = LiniaTransportMaritim::query()->where('ciutat_id', $solicitud->origen_id)->first() ?? LiniaTransportMaritim::query()->first();
        $tipusValidacio = TipusValidacio::firstOrCreate(['tipus' => 'Pendent']);
        $estatNova = EstatOferta::firstOrCreate(['estat' => 'Nova']);

        if (!$transportista || !$portOrigen || !$portDesti || !$linia) {
            return response()->json(['message' => 'No se pudo convertir la solicitud en oferta'], 422);
        }

        $oferta = new Oferta();
        $oferta->tipus_transport_id = $solicitud->tipus_transport_id;
        $oferta->comentaris = $solicitud->mercancia_nombre;
        $oferta->agent_comercial_id = $solicitud->operador_id;
        $oferta->transportista_origen_id = $transportista->id;
        $oferta->tipus_validacio_id = $tipusValidacio->id;
        $oferta->port_origen_id = $portOrigen->id;
        $oferta->port_desti_id = $portDesti->id;
        $oferta->linia_transport_maritim_id = $linia->id;
        $oferta->estat_oferta_id = $estatNova->id;
        $oferta->data_creacio = now()->toDateString();
        $oferta->data_validessa_inicial = now()->toDateString();
        $oferta->data_validessa_fina = now()->addDays(30)->toDateString();
        $oferta->solicitud_id = $solicitud->id;
        $oferta->documents_id = null;
        $oferta->etd = now()->addDays(7);
        $oferta->eta = now()->addDays(20);
        $oferta->transportista_destino_id = $transportista->id;
        $oferta->operador_id = $solicitud->operador_id;
        $oferta->acceptat = 0;
        $oferta->vist = 0;
        $oferta->acabat = 0;
        $oferta->cancelat = 0;
        $oferta->save();

        $notificacion = new Notificacion();
        $notificacion->id = (int) (Notificacion::max('id') ?? 0) + 1;
        $notificacion->usuari_id = $solicitud->operador_id;
        $notificacion->titulo = 'Solicitud Convertida en Oferta';
        $notificacion->descripcion = 'La solicitud ha sido aceptada y convertida en oferta';
        $notificacion->visto = 0;
        $notificacion->save();

        $solicitud->delete();

        return response()->json([
            'message' => 'Solicitud convertida en oferta correctamente',
            'oferta_id' => $oferta->id,
        ], 201);
    }
}
