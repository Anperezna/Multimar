<?php

namespace App\Http\Controllers;

use App\Models\TrackingStep;
use Illuminate\Http\Request;

class TrackingStepController extends Controller
{
    /**
     * Devuelve el catálogo completo de pasos disponibles.
     */
    public function index()
    {
        return response()->json(
            TrackingStep::orderBy('ordre')->orderBy('id')->get()
        );
    }
}