<?php

namespace App\Http\Controllers;

use App\Models\TipusFluxe;
use Illuminate\Http\Request;

class TipusFluxeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipusFluxes = TipusFluxe::all();

        return response()->json($tipusFluxes);
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
    public function show(TipusFluxe $tipusFluxe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipusFluxe $tipusFluxe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipusFluxe $tipusFluxe)
    {
        //
    }
}
