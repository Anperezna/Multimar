<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $crearSolicitud->client_id = $request->integer('client_id', 1);
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

        return response()->json(['message' => 'Solicitud creada exitosamente'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Solicitud $solicitud)
    {
        //
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
        //
    }
}
