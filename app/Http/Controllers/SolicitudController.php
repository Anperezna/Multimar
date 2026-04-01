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
        $crearSolicitud = new Solicitud;
        $crearSolicitud->mercancia_nombre = $request->input('nombreMercancia', 'Sin especificar');
        $crearSolicitud->pes_brut = (float) $request->input('pesoBruto', 0);
        $crearSolicitud->volum = (float) $request->input('volumen', 0);
        $crearSolicitud->client_id = (int) $request->input('client_id', 1);
        $crearSolicitud->operador_id = (int) $request->input('operador', 1);
        $crearSolicitud->mercancia_tipus = (int) $request->input('tipoMercancia', 1);
        $crearSolicitud->tipus_transport_id = (int) $request->input('tipus_transport_id', 1);
        $crearSolicitud->tipus_contenidor_id = (int) $request->input('tipoContenedor', 1);
        $crearSolicitud->origen_id = (int) $request->input('origen_id', $request->input('origen', 1));
        $crearSolicitud->destino_id = (int) $request->input('destino_id', $request->input('destino', 1));
        $crearSolicitud->incoterm_id = (int) $request->input('incoterm', 1);
        $crearSolicitud->tipus_fluxe_id = (int) $request->input('tipus_fluxe_id', 1);
        $crearSolicitud->tipus_carrega_id = (int) $request->input('tipoMercancia', 1);
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
