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
            'incoterm_id' => ['required', 'integer', 'exists:incoterms,id'],
            'ordre' => ['nullable', 'integer', 'min:1'],
        ]);

        $step = null;

        DB::beginTransaction();

        try {
            $ordre = $validated['ordre'] ?? ((int) (TrackingStep::where('incoterm_id', $validated['incoterm_id'])->max('ordre') ?? 0) + 1);

            $existingStep = TrackingStep::where('incoterm_id', $validated['incoterm_id'])
                ->where('ordre', $ordre)
                ->first();

            if ($existingStep) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Ya existe un paso con ese orden para este incoterm.',
                ], 422);
            }

            $step = new TrackingStep();
            $step->nom = $validated['nom'];
            $step->ordre = $ordre;
            $step->incoterm_id = $validated['incoterm_id'];
            $step->activo = true;
            $step->save();

            DB::commit();

            return response()->json($step, 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'No se pudo crear el paso.',
            ], 500);
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
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'ordre' => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();

        try {
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

            DB::commit();

            return $trackingStep->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'No se pudo actualizar el paso.',
            ], 500);
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
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'No se pudieron actualizar los estados.',
            ], 500);
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
            $replacementStep = TrackingStep::where('incoterm_id', $trackingStep->incoterm_id)
                ->where('id', '<>', $trackingStep->id)
                ->orderBy('ordre')
                ->first();

            $incotermsUsingStep = Incoterm::where('tracking_steps_id', $trackingStep->id)->exists();

            if ($incotermsUsingStep && ! $replacementStep) {
                DB::rollBack();

                return response()->json([
                    'message' => 'El incoterm debe conservar al menos un paso.',
                ], 409);
            }

            if ($replacementStep) {
                Incoterm::where('tracking_steps_id', $trackingStep->id)
                    ->update(['tracking_steps_id' => $replacementStep->id]);
            }

            TrackingStep::where('incoterm_id', $trackingStep->incoterm_id)
                ->where('ordre', '>', $trackingStep->ordre)
                ->decrement('ordre');

            $trackingStep->delete();
            DB::commit();

            return ['message' => 'Paso eliminado correctamente.'];
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'No se pudo eliminar el paso.',
            ], 500);
        }
    }
}
