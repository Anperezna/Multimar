<?php

namespace App\Http\Controllers;

use App\Models\TrackingStep;
use App\Models\Incoterm;
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

        $step = null;

        DB::transaction(function () use ($validated, &$step) {
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
        });

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

        return DB::transaction(function () use ($validated, $trackingStep) {
            $oldOrder = (int) $trackingStep->ordre;
            $newOrder = (int) $validated['ordre'];
            $incotermId = (int) $trackingStep->incoterm_id;

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

            return $trackingStep->fresh();
        }, 5);
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

        DB::transaction(function () use ($validated) {
            foreach ($validated['pasos'] as $pasoData) {
                $paso = TrackingStep::findOrFail($pasoData['id']);
                $paso->activo = $pasoData['activo'];
                $paso->save();
            }
        }, 5);

        return ['message' => 'Estados actualizados correctamente.'];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrackingStep $trackingStep)
    {
        DB::transaction(function () use ($trackingStep) {
            // Buscar un paso de reemplazo para los incoterms que usen este paso
            $replacementStep = TrackingStep::where('incoterm_id', $trackingStep->incoterm_id)
                ->where('id', '<>', $trackingStep->id)
                ->orderBy('ordre')
                ->first();

            // Verificar si hay incoterms que dependan de este paso
            $incotermsUsingStep = Incoterm::where('tracking_steps_id', $trackingStep->id)->exists();

            if ($incotermsUsingStep && ! $replacementStep) {
                abort(409, 'El incoterm debe conservar al menos un paso.');
            }

            // Si hay incoterms usando este paso, reasignarlos al paso de reemplazo
            if ($replacementStep) {
                Incoterm::where('tracking_steps_id', $trackingStep->id)
                    ->update(['tracking_steps_id' => $replacementStep->id]);
            }

            // Reordenar pasos posteriores
            TrackingStep::where('incoterm_id', $trackingStep->incoterm_id)
                ->where('ordre', '>', $trackingStep->ordre)
                ->decrement('ordre');

            $trackingStep->delete();
        }, 5);

        return ['message' => 'Paso eliminado correctamente.'];
    }
}
