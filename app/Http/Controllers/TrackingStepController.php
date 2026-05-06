<?php

namespace App\Http\Controllers;

use App\Models\TrackingStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingStepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->filled('incoterm_id')) {
            $steps = TrackingStep::where('incoterm_id', (int) $request->incoterm_id)
                ->orderBy('ordre')
                ->get();
        } else {
            $steps = TrackingStep::orderBy('ordre')->get();
        }

        return $steps;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'incoterm_id' => ['required', 'integer'],
            'ordre' => ['required', 'integer'],
        ]);

        $existingStep = TrackingStep::where('incoterm_id', $validated['incoterm_id'])
            ->where('ordre', $validated['ordre'])
            ->first();

        if ($existingStep) {
            abort(422, 'Ya existe un paso con ese orden para este incoterm.');
        }

        $step = new TrackingStep();
        $step->nom = $validated['nom'];
        $step->ordre = $validated['ordre'];
        $step->incoterm_id = $validated['incoterm_id'];
        $step->save();

        return $step;
    }

    /**
     * Display the specified resource.
     */
    public function show(TrackingStep $trackingStep)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TrackingStep $trackingStep)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'ordre' => ['required', 'integer', 'min:1'],
        ]);

        $oldOrder = (int) $trackingStep->ordre;
        $newOrder = (int) $validated['ordre'];
        $incotermId = (int) $trackingStep->incoterm_id;

        DB::beginTransaction();

        try {
            if ($newOrder !== $oldOrder) {
                if ($newOrder < $oldOrder) {
                    // Subir: desplazar hacia abajo los pasos entre newOrder y oldOrder-1
                    TrackingStep::where('incoterm_id', $incotermId)
                        ->where('id', '<>', $trackingStep->id)
                        ->where('ordre', '>=', $newOrder)
                        ->where('ordre', '<', $oldOrder)
                        ->increment('ordre');
                } else {
                    // Bajar: desplazar hacia arriba los pasos entre oldOrder+1 y newOrder
                    TrackingStep::where('incoterm_id', $incotermId)
                        ->where('id', '<>', $trackingStep->id)
                        ->where('ordre', '>', $oldOrder)
                        ->where('ordre', '<=', $newOrder)
                        ->decrement('ordre');
                }

                $trackingStep->ordre = $newOrder;
            }

            $trackingStep->nom = $validated['nom'];
            $trackingStep->save();

            DB::commit();

            return $trackingStep->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, 'No se pudo actualizar el paso.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrackingStep $trackingStep)
    {
        DB::beginTransaction();

        try {
            $replacementStep = TrackingStep::where('incoterm_id', $trackingStep->incoterm_id)
                ->where('id', '<>', $trackingStep->id)
                ->orderBy('ordre')
                ->first();

            $incotermsUsingStep = DB::table('incoterms')
                ->where('tracking_steps_id', $trackingStep->id)
                ->exists();

            if ($incotermsUsingStep && ! $replacementStep) {
                DB::rollBack();

                abort(409, 'El incoterm debe conservar al menos un paso.');
            }

            if ($replacementStep) {
                DB::table('incoterms')
                    ->where('tracking_steps_id', $trackingStep->id)
                    ->update(['tracking_steps_id' => $replacementStep->id]);
            }

            TrackingStep::where('incoterm_id', $trackingStep->incoterm_id)
                ->where('ordre', '>', $trackingStep->ordre)
                ->decrement('ordre');

            $trackingStep->delete();

            DB::commit();

            return ['message' => 'Paso eliminado correctamente.'];
        } catch (\Exception $e) {
            DB::rollBack();

            abort(500, 'No se pudo eliminar el paso.');
        }
    }
}
