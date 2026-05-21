<?php

namespace App\Http\Controllers;

use App\Models\TipusIncoterm;
use App\Models\TrackingStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncotermController extends Controller
{
    /**
     * Obtener el listado completo con sus pasos asociados.
     */
    public function index()
    {
        // Retornamos los TipusIncoterm ordenados por código con su relación cargada
        return response()->json(
            TipusIncoterm::with('trackingSteps')
                        ->orderBy('codi')
                        ->get()
        );
    }

    /**
     * Guardar un nuevo Incoterm junto con sus pasos seleccionados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codi'  => 'required|string|max:50',
            'nom'   => 'required|string|max:255',
            'pasos' => 'array' // Array de IDs de tracking_steps seleccionados en el formulario
        ]);

        DB::beginTransaction();
        try {
            // 1. Crear la entidad catálogo base
            $tipusIncoterm = TipusIncoterm::create([
                'codi' => $request->input('codi'),
                'nom'  => $request->input('nom'),
            ]);

            // 2. Asociar los pasos mediante sync() en la tabla pivot
            if ($request->has('pasos')) {
                $tipusIncoterm->trackingSteps()->sync($request->input('pasos'));
            }

            DB::commit();
            return response()->json($tipusIncoterm->load('trackingSteps'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el incoterm: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar los datos maestros y sincronizar los nuevos pasos.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'codi'  => 'required|string|max:50',
            'nom'   => 'required|string|max:255',
            'pasos' => 'array'
        ]);

        $tipusIncoterm = TipusIncoterm::find($id);

        if (!$tipusIncoterm) {
            return response()->json(['message' => 'Incoterm no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            // 1. Actualizar campos nativos del catálogo
            $tipusIncoterm->update([
                'codi' => $request->input('codi'),
                'nom'  => $request->input('nom'),
            ]);

            // 2. Sincronizar la tabla pivot de manera exacta
            $tipusIncoterm->trackingSteps()->sync($request->input('pasos', []));

            DB::commit();
            return response()->json($tipusIncoterm->load('trackingSteps'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar el Incoterm y limpiar la tabla pivot.
     */
    public function destroy($id)
    {
        $tipusIncoterm = TipusIncoterm::find($id);

        if (!$tipusIncoterm) {
            return response()->json(['message' => 'Incoterm no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            // 1. Limpiar primero las relaciones en la tabla pivot incoterms
            $tipusIncoterm->trackingSteps()->detach();

            // 2. Eliminar el registro padre
            $tipusIncoterm->delete();

            DB::commit();
            return response()->json(['message' => 'Incoterm eliminado correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint auxiliar para listar todos los pasos disponibles en el sistema.
     */
    public function getAvailableSteps()
    {
        return response()->json(
            TrackingStep::orderBy('ordre')->orderBy('id')->get()
        );
    }
}