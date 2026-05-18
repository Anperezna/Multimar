<?php

namespace App\Http\Controllers;

use App\Models\Incoterm;
use App\Models\Solicitud;
use App\Models\TipusIncoterm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Classes\Utilitat;

class IncotermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Devolver los modelos tal cual con la relación cargada.
        return Incoterm::with('tipusIncoterm')->get();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $tipusIncotermId = $request->input('tipus_inconterm_id');

            if ($request->input('nom') !== null && $request->input('codi') !== null) {
                $tipusIncoterm = new TipusIncoterm();
                $tipusIncoterm->nom = $request->input('nom');
                $tipusIncoterm->codi = $request->input('codi');
                $tipusIncoterm->save();

                $tipusIncotermId = $tipusIncoterm->id;
            }

            $incoterm = new Incoterm();
            $incoterm->tipus_inconterm_id = $tipusIncotermId;
            $incoterm->tracking_steps_id = $request->tracking_steps_id;
            $incoterm->save();

            if ($incoterm->tracking_steps_id) {
                $step = TrackingStep::find($incoterm->tracking_steps_id);
                if ($step) {
                    $step->incoterm_id = $incoterm->id;
                    $step->save();
                }
            }

            DB::commit();

            return $incoterm;
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Incoterm $incoterm)
    {
        // Cargar relaciones necesarias y ordenar trackingSteps por 'ordre'
        $incoterm->load([
            'tipusIncoterm',
            'trackingStep',
            'trackingSteps' => function ($q) {
                $q->orderBy('ordre');
            },
        ]);

        return $incoterm;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Incoterm $incoterm)
    {
        DB::beginTransaction();

        try {
            if ($request->input('nom') !== null && $request->input('codi') !== null) {
                $tipusIncoterm = $incoterm->tipusIncoterm;

                if (! $tipusIncoterm) {
                    $tipusIncoterm = new TipusIncoterm();
                }

                $tipusIncoterm->nom = $request->input('nom');
                $tipusIncoterm->codi = $request->input('codi');
                $tipusIncoterm->save();

                $incoterm->tipus_inconterm_id = $tipusIncoterm->id;
            } elseif ($request->has('tipus_inconterm_id')) {
                $incoterm->tipus_inconterm_id = $request->tipus_inconterm_id;
            }

            $incoterm->save();

            DB::commit();

            return $incoterm;
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incoterm $incoterm)
    {
        DB::beginTransaction();

        try {
            $tieneSolicitudes = Solicitud::where('incoterm_id', $incoterm->id)->exists();

            if ($tieneSolicitudes) {
                throw new \Exception('No se puede eliminar este incoterm porque hay solicitudes que lo utilizan.', 1494);
            }

            $incoterm->delete();

            DB::commit();

            return ['message' => 'Incoterm eliminado correctamente.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }

    /**
     * Alias para ver incoterms (reemplaza a index para uso administrativo).
     */
    public function verIncoterms()
    {
        return $this->index();
    }
}
