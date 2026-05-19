<?php

namespace App\Http\Controllers;

use App\Models\TrackingStep;
use App\Models\Incoterm;
use App\Classes\Utilitat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingStepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $steps = TrackingStep::all();
        return $steps;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $step = new TrackingStep();
            $step->nom = $request->nom;
            $step->ordre = $request->ordre;
            $step->incoterm_id = $request->incoterm_id;
            $step->activo = $request->input('activo', true);
            $step->save();

            DB::commit();

            return $step;
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TrackingStep $trackingStep)
    {
        return $trackingStep;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TrackingStep $trackingStep)
    {
        DB::beginTransaction();

        try {
            $trackingStep->nom = $request->nom;
            $trackingStep->ordre = $request->ordre;
            $trackingStep->incoterm_id = $request->incoterm_id;
            $trackingStep->activo = $request->input('activo', $trackingStep->activo);
            $trackingStep->save();

            DB::commit();

            return $trackingStep;
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }

    /**
     * Actualizar estados de múltiples pasos (activo/inactivo)
     */
    public function actualizarEstados(Request $request)
    {
        $validated = $request->validate([
            'pasos' => ['required', 'array'],
            'pasos.*.id' => ['required', 'integer'],
            'pasos.*.activo' => ['required', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['pasos'] as $pasoData) {
                $paso = TrackingStep::findOrFail($pasoData['id']);
                $paso->activo = $pasoData['activo'];
                $paso->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }

        return ['message' => 'Estados actualizados correctamente.'];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrackingStep $trackingStep)
    {
        DB::beginTransaction();

        try {
            $trackingStep->delete();
            DB::commit();

            return ['message' => 'Paso eliminado correctamente.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }

    /**
     * Asignar el paso principal de un incoterm.
     */
    public function assignPrincipalStep(Request $request, Incoterm $incoterm)
    {
        DB::beginTransaction();

        try {
            $step = TrackingStep::find($request->tracking_steps_id);

            if (! $step) {
                throw new \Exception('Paso de tracking no encontrado.', 1498);
            }

            $step->incoterm_id = $incoterm->id;
            $step->save();

            $incoterm->tracking_steps_id = $step->id;
            $incoterm->save();

            DB::commit();

            return $incoterm;
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }
}
