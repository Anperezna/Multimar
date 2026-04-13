<?php

namespace App\Http\Controllers;

use App\Models\EstatOferta;
use App\Models\Notificacion;
use App\Models\Oferta;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    private function resolveOfertaStatus($oferta): string
    {
        $isCanceled = (int) ($oferta->cancelat ?? 0) === 1;
        $isAccepted = (int) ($oferta->acceptat ?? 0) === 1;

        if ($isCanceled) {
            return 'Cancelada';
        }

        if ($isAccepted) {
            return 'Aceptada';
        }

        return trim((string) ($oferta->estat_oferta_nom ?? 'Nova'));
    }

    private function ofertaQuery()
    {
        return Oferta::query()
            ->leftJoin('tipus_transports as tt', 'ofertes.tipus_transport_id', '=', 'tt.id')
            ->leftJoin('transportistes as tr', 'ofertes.transportista_origen_id', '=', 'tr.id')
            ->leftJoin('estats_ofertes as eo', 'ofertes.estat_oferta_id', '=', 'eo.id')
            ->leftJoin('solicitud as so', 'ofertes.solicitud_id', '=', 'so.id')
            ->leftJoin('tipus_fluxes as tf', 'so.tipus_fluxe_id', '=', 'tf.id')
            ->leftJoin('ciutats as co', 'so.origen_id', '=', 'co.id')
            ->leftJoin('ciutats as cd', 'so.destino_id', '=', 'cd.id')
            ->leftJoin('usuaris as u', 'so.operador_id', '=', 'u.id')
            ->leftJoin('tipus_contenidors as tcon', 'so.tipus_contenidor_id', '=', 'tcon.id')
            ->leftJoin('tipus_carrega as tcar', 'so.tipus_carrega_id', '=', 'tcar.id')
            ->leftJoin('incoterms as i', 'so.incoterm_id', '=', 'i.id')
            ->leftJoin('tipus_incoterms as ti', 'i.tipus_inconterm_id', '=', 'ti.id')
            ->select([
                'ofertes.id',
                'ofertes.comentaris',
                'ofertes.data_creacio',
                'ofertes.etd',
                'ofertes.eta',
                'ofertes.acceptat',
                'ofertes.vist',
                'ofertes.acabat',
                'ofertes.cancelat',
                'tt.tipus as tipus_transport_nom',
                'tr.nom as transportista_origen_nom',
                'eo.estat as estat_oferta_nom',
                'tf.tipus as operacio_nom',
                'so.mercancia_nombre',
                'so.pes_brut',
                'so.volum',
                'tcon.tipus as tipus_contenidor_nom',
                'tcar.tipus as tipus_carrega_nom',
                'co.nom as origen_nom',
                'cd.nom as destino_nom',
                'u.nom as operador_nom',
                'u.cognoms as operador_cognoms',
                'i.id as incoterm_id',
                'ti.codi as incoterm_codi',
                'ti.nom as incoterm_nom',
            ]);
    }

    private function toFrontendOferta($oferta)
    {
        $id = (int) $oferta->id;

        return [
            'id' => $id,
            'code' => (string) $id,
            'kind' => 'Oferta',
            'description' => trim((string) ($oferta->comentaris ?? '')),
            'operation' => trim((string) ($oferta->operacio_nom ?? '')),
            'status' => $this->resolveOfertaStatus($oferta),
            'carrier' => trim((string) ($oferta->transportista_origen_nom ?? '')),
            'lastUpdate' => $oferta->etd ?: $oferta->eta ?: $oferta->data_creacio,
            'mercanciaNombre' => trim((string) ($oferta->mercancia_nombre ?? '')),
            'pesoBrut' => $oferta->pes_brut,
            'volum' => $oferta->volum,
            'tipusTransportNom' => trim((string) ($oferta->tipus_transport_nom ?? '')),
            'tipusContenidorNom' => trim((string) ($oferta->tipus_contenidor_nom ?? '')),
            'tipusCarregaNom' => trim((string) ($oferta->tipus_carrega_nom ?? '')),
            'origenNom' => trim((string) ($oferta->origen_nom ?? '')),
            'destinoNom' => trim((string) ($oferta->destino_nom ?? '')),
            'operadorNom' => trim((string) (($oferta->operador_nom ?? '') . ' ' . ($oferta->operador_cognoms ?? ''))),
            'incotermText' => trim((string) (($oferta->incoterm_codi ?? '') . ' ' . ($oferta->incoterm_nom ?? ''))),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\Usuari|null $usuari */
        $usuari = request()->user();

        if (! $usuari) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $usuari->load('rol');
        $rol = mb_strtolower(trim((string) ($usuari->rol?->rol ?? '')));

        $query = $this->ofertaQuery();

        if ($rol === 'operador') {
            $query->where('ofertes.operador_id', $usuari->id);
        }

        if ($rol === 'usuari') {
            $query->where('so.client_id', $usuari->id);
        }

        $ofertes = $query
            ->orderByDesc('ofertes.id')
            ->get()
            ->map(fn ($oferta) => $this->toFrontendOferta($oferta))
            ->values();

        return response()->json($ofertes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $oferta = $this->ofertaQuery()
            ->where('ofertes.id', $id)
            ->first();

        if (!$oferta) {
            return response()->json(['message' => 'Oferta no encontrada'], 404);
        }

        return response()->json($this->toFrontendOferta($oferta));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Oferta $oferta)
    {
        $request->validate([
            'comentaris' => ['nullable', 'string', 'max:255'],
        ]);

        $oferta->comentaris = $request->input('comentaris', $oferta->comentaris);
        $oferta->save();

        return response()->json($this->toFrontendOferta($oferta));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Oferta $oferta)
    {
        $oferta->delete();

        return response()->noContent();
    }

    public function accept(Oferta $oferta)
    {
        $estatAcceptada = EstatOferta::query()->firstOrCreate([
            'estat' => 'Acceptada',
        ]);

        $oferta->acceptat = 1;
        $oferta->cancelat = 0;
        $oferta->acabat = $oferta->acabat ?? 0;
        $oferta->estat_oferta_id = $estatAcceptada->id;
        $oferta->save();

        $recipientId = (int) ($oferta->operador_id ?? 0);
        if ($recipientId <= 0) {
            $solicitud = Solicitud::query()->where('id', $oferta->solicitud_id)->first();
            $recipientId = (int) ($solicitud->operador_id ?? 0);
        }

        if ($recipientId > 0) {
            $notificacion = new Notificacion();
            $notificacion->id = (int) (Notificacion::max('id') ?? 0) + 1;
            $notificacion->usuari_id = $recipientId;
            $notificacion->titulo = 'Oferta Aceptada';
            $notificacion->descripcion = 'La oferta #' . $oferta->id . ' ha sido aceptada';
            $notificacion->visto = 0;
            $notificacion->save();
        }

        $ofertaActualitzada = $this->ofertaQuery()
            ->where('ofertes.id', $oferta->id)
            ->first();

        if (! $ofertaActualitzada) {
            return response()->json([
                'message' => 'Oferta aceptada correctamente',
                'id' => $oferta->id,
            ]);
        }

        return response()->json($this->toFrontendOferta($ofertaActualitzada));
    }

    public function cancel(Oferta $oferta)
    {
        $estatCancelada = EstatOferta::query()->firstOrCreate([
            'estat' => 'Cancelada',
        ]);

        $oferta->cancelat = 1;
        $oferta->acceptat = 0;
        $oferta->estat_oferta_id = $estatCancelada->id;
        $oferta->save();

        $recipientId = (int) ($oferta->operador_id ?? 0);
        if ($recipientId <= 0) {
            $solicitud = Solicitud::query()->where('id', $oferta->solicitud_id)->first();
            $recipientId = (int) ($solicitud->operador_id ?? 0);
        }

        if ($recipientId > 0) {
            $notificacion = new Notificacion();
            $notificacion->id = (int) (Notificacion::max('id') ?? 0) + 1;
            $notificacion->usuari_id = $recipientId;
            $notificacion->titulo = 'Oferta Cancelada';
            $notificacion->descripcion = 'La oferta #' . $oferta->id . ' ha sido cancelada';
            $notificacion->visto = 0;
            $notificacion->save();
        }

        $ofertaActualitzada = $this->ofertaQuery()
            ->where('ofertes.id', $oferta->id)
            ->first();

        if (! $ofertaActualitzada) {
            return response()->json([
                'message' => 'Oferta cancelada correctamente',
                'id' => $oferta->id,
            ]);
        }

        return response()->json($this->toFrontendOferta($ofertaActualitzada));
    }
}
