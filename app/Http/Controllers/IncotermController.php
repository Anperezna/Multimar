<?php

namespace App\Http\Controllers;

use App\Models\Incoterm;
use App\Models\TipusIncoterm;
use App\Models\TrackingStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncotermController extends Controller
{
    public function index()
    {
        $incoterms = Incoterm::query()
            ->with('tipusIncoterm:id,codi,nom')
            ->withCount('trackingSteps')
            ->get();

        $resultado = $incoterms->map(function (Incoterm $incoterm) {
            $tipus = $incoterm->tipusIncoterm;

            return [
                'id' => $incoterm->id,
                'label' => $tipus
                    ? trim(($tipus->codi ?? '').' - '.($tipus->nom ?? ''))
                    : 'Incoterm '.$incoterm->id,
                'tipus_inconterm_id' => $incoterm->tipus_inconterm_id,
                'tracking_steps_id' => $incoterm->tracking_steps_id,
                'pasos_totales' => (int) ($incoterm->tracking_steps_count ?? 0),
            ];
        })->values();

        return $resultado;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipus_inconterm_id' => ['required', 'integer'],
            'nom_primer_paso' => ['nullable', 'string', 'max:255'],
        ]);

        $tipusIncoterm = TipusIncoterm::find((int) $validated['tipus_inconterm_id']);

        if (! $tipusIncoterm) {
            $resultado = response()->json([
                'message' => 'No existe el tipo de incoterm seleccionado.',
            ], 422);

            return $resultado;
        }

        DB::beginTransaction();

        try {
            $primerPaso = new TrackingStep();
            $primerPaso->nom = trim((string) ($validated['nom_primer_paso'] ?? '')) ?: 'Paso inicial';
            $primerPaso->ordre = 1;
            $primerPaso->activo = true;
            $primerPaso->save();

            $incoterm = new Incoterm();
            $incoterm->tipus_inconterm_id = $tipusIncoterm->id;
            $incoterm->tracking_steps_id = $primerPaso->id;
            $incoterm->save();

            $primerPaso->incoterm_id = $incoterm->id;
            $primerPaso->save();

            DB::commit();

            $resultado = [
                'id' => $incoterm->id,
                'label' => trim(($tipusIncoterm->codi ?? '').' - '.($tipusIncoterm->nom ?? '')),
                'tipus_inconterm_id' => $incoterm->tipus_inconterm_id,
                'tracking_steps_id' => $incoterm->tracking_steps_id,
            ];

            return $resultado;
        } catch (\Throwable $e) {
            DB::rollBack();

            $resultado = response()->json([
                'message' => 'No se pudo crear el incoterm.',
            ], 500);

            return $resultado;
        }
    }

    public function show(Incoterm $incoterm)
    {
        $incoterm->load([
            'tipusIncoterm:id,codi,nom',
            'trackingStep:id,nom,ordre,activo,incoterm_id',
            'trackingSteps' => function ($consulta) {
                $consulta->orderBy('ordre');
            },
        ]);

        $resultado = [
            'id' => $incoterm->id,
            'tipus_inconterm_id' => $incoterm->tipus_inconterm_id,
            'tracking_steps_id' => $incoterm->tracking_steps_id,
            'tipus' => $incoterm->tipusIncoterm ? [
                'id' => $incoterm->tipusIncoterm->id,
                'codi' => $incoterm->tipusIncoterm->codi,
                'nom' => $incoterm->tipusIncoterm->nom,
            ] : null,
            'tracking' => $incoterm->trackingStep ? [
                'id' => $incoterm->trackingStep->id,
                'nom' => $incoterm->trackingStep->nom,
                'ordre' => $incoterm->trackingStep->ordre,
            ] : null,
            'trackingSteps' => $incoterm->trackingSteps->map(function (TrackingStep $paso) {
                return [
                    'id' => $paso->id,
                    'nom' => $paso->nom,
                    'ordre' => $paso->ordre,
                    'activo' => (bool) $paso->activo,
                ];
            })->values(),
        ];

        return $resultado;
    }

    public function update(Request $request, Incoterm $incoterm)
    {
        $validated = $request->validate([
            'tipus_inconterm_id' => ['required', 'integer'],
            'tracking_steps_id' => ['required', 'integer'],
        ]);

        $tipusIncoterm = TipusIncoterm::find((int) $validated['tipus_inconterm_id']);
        $trackingStep = TrackingStep::find((int) $validated['tracking_steps_id']);

        if (! $tipusIncoterm || ! $trackingStep) {
            $resultado = response()->json([
                'message' => 'No se pudieron validar los datos del incoterm.',
            ], 422);

            return $resultado;
        }

        $incoterm->tipus_inconterm_id = $tipusIncoterm->id;
        $incoterm->tracking_steps_id = $trackingStep->id;
        $incoterm->save();

        $resultado = [
            'message' => 'Incoterm actualizado correctamente.',
        ];

        return $resultado;
    }

    public function destroy(Incoterm $incoterm)
    {
        try {
            $incoterm->delete();

            $resultado = [
                'message' => 'Incoterm eliminado correctamente.',
            ];

            return $resultado;
        } catch (\Throwable $e) {
            $resultado = response()->json([
                'message' => 'No se pudo eliminar el incoterm.',
            ], 500);

            return $resultado;
        }
    }
}