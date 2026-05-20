<?php

namespace App\Http\Controllers;

use App\Models\TrackingStep;
use App\Models\Incoterm;
use App\Models\TipusTracking;
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
        if (empty($request->input('incoterm_id'))) {
            return TrackingStep::orderBy('ordre')->orderBy('id')->get();
        }

        $incotermId = (int) $request->input('incoterm_id');
        $incoterm = Incoterm::with('tipusIncoterm')->find($incotermId);

        if (! $incoterm?->tipusIncoterm) {
            return [];
        }

        $idsPasosActivos = $incoterm->tipusIncoterm->trackingSteps()
            ->pluck('tracking_steps.id')
            ->all();

        return TrackingStep::orderBy('ordre')
            ->orderBy('id')
            ->get()
            ->map(function ($paso) use ($incotermId, $idsPasosActivos) {
                $paso->incoterm_id = $incotermId;
                $paso->activo = in_array($paso->id, $idsPasosActivos, true);
                return $paso;
            });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $paso = new TrackingStep();
            $paso->nom = $request->nom;
            $paso->ordre = $request->ordre;
            $paso->save();

            if (!empty($request->input('incoterm_id'))) {
                $incoterm = Incoterm::with('tipusIncoterm')->find($request->input('incoterm_id'));
                $tipusIncoterm = $incoterm?->tipusIncoterm;

                if ($tipusIncoterm !== null) {
                    TipusTracking::firstOrCreate([
                        'tipus_incoterm_id' => $tipusIncoterm->id,
                        'tracking_step_id' => $paso->id,
                    ]);
                }
            }

            $paso->incoterm_id = $request->input('incoterm_id');
            $paso->activo = true;

            DB::commit();

            return $paso;
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
            $trackingStep->save();

            $trackingStep->incoterm_id = $request->input('incoterm_id');
            $trackingStep->activo = true;

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
        $validados = $request->validate([
            'incoterm_id' => ['required', 'integer'],
            'pasos' => ['required', 'array'],
            'pasos.*.id' => ['required', 'integer'],
            'pasos.*.activo' => ['required', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            $incoterm = Incoterm::with('tipusIncoterm')->find($validados['incoterm_id']);
            $tipusIncoterm = $incoterm?->tipusIncoterm;

            if (! $tipusIncoterm) {
                throw new \Exception(Utilitat::errorMessage(1503), 1503);
            }

            $pasosRecibidos = collect($validados['pasos']);

            $idsPasosActivos = $pasosRecibidos
                ->where('activo', true)
                ->pluck('id')
                ->all();

            $idsPasosInactivos = $pasosRecibidos
                ->where('activo', false)
                ->pluck('id')
                ->all();

            if (!empty($idsPasosActivos)) {
                $tipusIncoterm->trackingSteps()->syncWithoutDetaching($idsPasosActivos);
            }

            if (!empty($idsPasosInactivos)) {
                TipusTracking::where('tipus_incoterm_id', $tipusIncoterm->id)
                    ->whereIn('tracking_step_id', $idsPasosInactivos)
                    ->delete();
            }

            $primerPaso = $tipusIncoterm->trackingSteps()
                ->orderBy('ordre')
                ->orderBy('id')
                ->first();
            $incoterm->tracking_steps_id = $primerPaso?->id;
            $incoterm->save();

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
            TipusTracking::where('tracking_step_id', $trackingStep->id)->delete();

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
                throw new \Exception(Utilitat::errorMessage(1498), 1498);
            }

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
