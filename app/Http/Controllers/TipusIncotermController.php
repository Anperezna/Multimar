<?php

namespace App\Http\Controllers;

use App\Models\TipusIncoterm;

class TipusIncotermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiposIncoterm = TipusIncoterm::orderBy('codi')
            ->get(['id', 'codi', 'nom'])
            ->map(function (TipusIncoterm $tipusIncoterm) {
                return [
                    'id' => $tipusIncoterm->id,
                    'codi' => $tipusIncoterm->codi,
                    'nom' => $tipusIncoterm->nom,
                    'label' => trim(($tipusIncoterm->codi ?? '').' - '.($tipusIncoterm->nom ?? '')),
                ];
            })
            ->values();

        return $tiposIncoterm;
    }
}
