<?php

namespace App\Http\Controllers;

use App\Models\Incoterm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncotermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Incoterm::query()
            ->with('tipusIncoterm:id,codi,nom')
            ->get()
            ->map(function (Incoterm $incoterm) {
                $tipus = $incoterm->tipusIncoterm;

                return [
                    'id' => $incoterm->id,
                    'label' => $tipus
                        ? trim(($tipus->codi ?? '').' - '.($tipus->nom ?? ''))
                        : 'Incoterm '.$incoterm->id,
                ];
            })
            ->values();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipus_inconterm_id' => ['required', 'integer'],
            'tracking_steps_id' => ['required', 'integer'],
        ]);

        DB::beginTransaction();

        try {
            // Crear el registro con Eloquent usando save()
            $incoterm = new Incoterm;
            $incoterm->tipus_inconterm_id = $validated['tipus_inconterm_id'];
            $incoterm->tracking_steps_id = $validated['tracking_steps_id'];
            $incoterm->save();

            // Confirmar la transacción
            DB::commit();

            // Devolver el modelo creado
            return $incoterm;
        } catch (\Exception $e) {
            // Si algo falla, deshacer cambios en la base de datos
            DB::rollBack();

            $mensaje = response('No se pudo crear el incoterm', 500);

            return $mensaje;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Incoterm $incoterm)
    {
        return [
            'id' => $incoterm->id,
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
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Incoterm $incoterm)
    {
        $validated = $request->validate([
            'tipus_inconterm_id' => ['required', 'integer'],
            'tracking_steps_id' => ['required', 'integer'],
        ]);

        $incoterm->tipus_inconterm_id = $validated['tipus_inconterm_id'];
        $incoterm->tracking_steps_id = $validated['tracking_steps_id'];
        $incoterm->save();

        return $incoterm;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incoterm $incoterm)
    {
        $incoterm->delete();

        return ['message' => 'Incoterm eliminado correctamente.'];
    }

    /**
     * Alias para ver incoterms (reemplaza a index para uso administrativo).
     */
    public function verIncoterms()
    {
        return $this->index();
    }
}
