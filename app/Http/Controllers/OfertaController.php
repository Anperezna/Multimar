<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ofertes = Oferta::query()
            ->leftJoin('tipus_transports as tt', 'ofertes.tipus_transport_id', '=', 'tt.id')
            ->leftJoin('transportistes as tr', 'ofertes.transportista_origen_id', '=', 'tr.id')
            ->leftJoin('estats_ofertes as eo', 'ofertes.estat_oferta_id', '=', 'eo.id')
            ->leftJoin('solicitud as so', 'ofertes.solicitud_id', '=', 'so.id')
            ->leftJoin('tipus_fluxes as tf', 'so.tipus_fluxe_id', '=', 'tf.id')
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
            ])
            ->orderByDesc('ofertes.id')
            ->get();

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
    public function show(Oferta $oferta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Oferta $oferta)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Oferta $oferta)
    {
        //
    }
}
