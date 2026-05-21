<?php

namespace App\Http\Controllers;

use App\Classes\Utilitat;
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
        try {
            return response()->json(
                TipusIncoterm::with('trackingSteps')
                            ->orderBy('codi')
                            ->get()
            );
        } catch (\Exception $e) {
            // Si el servidor de BD se cae, Utilitat lo atrapa
            return response()->json(['message' => Utilitat::errorMessage($e)], 500);
        }
    }

    /**
     * Guardar un nuevo Incoterm junto con sus pasos seleccionados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codi'  => 'required|string|max:50',
            'nom'   => 'required|string|max:255',
            'pasos' => 'array' 
        ]);

        DB::beginTransaction();
        try {
            $tipusIncoterm = TipusIncoterm::create([
                'codi' => $request->input('codi'),
                'nom'  => $request->input('nom'),
            ]);

            if ($request->has('pasos')) {
                $tipusIncoterm->trackingSteps()->sync($request->input('pasos'));
            }

            DB::commit();
            return response()->json($tipusIncoterm->load('trackingSteps'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => Utilitat::errorMessage($e)], 500);
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

        DB::beginTransaction();
        try {
            $tipusIncoterm = TipusIncoterm::find($id);

            // Validación manual: Lanzamos la excepción con tu código personalizado 1504
            if (!$tipusIncoterm) {
                throw new \Exception('Incoterm no encontrado', 1504);
            }

            $tipusIncoterm->update([
                'codi' => $request->input('codi'),
                'nom'  => $request->input('nom'),
            ]);

            $tipusIncoterm->trackingSteps()->sync($request->input('pasos', []));

            DB::commit();
            return response()->json($tipusIncoterm->load('trackingSteps'));

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Si el código es 1500 devolvemos error 404, para el resto 500
            $status = ($e->getCode() == 1504) ? 404 : 500;
            return response()->json(['message' => Utilitat::errorMessage($e)], $status);
        }
    }

    /**
     * Eliminar el Incoterm y limpiar la tabla pivot.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $tipusIncoterm = TipusIncoterm::find($id);

            // Validación manual: Lanzamos la excepción con tu código personalizado 1504
            if (!$tipusIncoterm) {
                throw new \Exception('', 1504);
            }

            $tipusIncoterm->trackingSteps()->detach();
            $tipusIncoterm->delete();

            DB::commit();
            return response()->json(['message' => 'Incoterm eliminado correctamente'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Ajustamos el status HTTP según el tipo de error
            $status = 500;
            if ($e->getCode() == 1504) {
                $status = 404; // No encontrado
            } elseif (isset($e->errorInfo[1]) && $e->errorInfo[1] == 547) {
                $status = 409; // Conflicto (El famoso error de llaves foráneas de SQL Server)
            }

            return response()->json(['message' => Utilitat::errorMessage($e)], $status);
        }
    }

    /**
     * Endpoint auxiliar para listar todos los pasos disponibles en el sistema.
     */
    public function getAvailableSteps()
    {
        try {
            return response()->json(
                TrackingStep::orderBy('ordre')->orderBy('id')->get()
            );
        } catch (\Exception $e) {
            return response()->json(['message' => Utilitat::errorMessage($e)], 500);
        }
    }
}