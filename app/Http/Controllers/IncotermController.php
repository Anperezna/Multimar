<?php

namespace App\Http\Controllers;

use App\Models\Incoterm;
use Illuminate\Http\Request;

class IncotermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Incoterm::query()
            ->with('tipusIncoterm:id,codi,nom')
            ->get()
            ->map(function (Incoterm $incoterm) {
                $tipus = $incoterm->tipusIncoterm;

                return [
                    'id' => $incoterm->id,
                    'label' => $tipus
                        ? trim(($tipus->codi ?? '').' - '.($tipus->nom ?? ''))
                        : 'Incoterm '.$incoterm->id,
                ];
            })
            ->values();
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
    public function show(Incoterm $incoterm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Incoterm $incoterm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incoterm $incoterm)
    {
        //
    }
}
