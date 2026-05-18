<?php

namespace App\Http\Controllers;

use App\Classes\Utilitat;
use App\Models\LiniaTransportMaritim;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class LiniaTransportMaritimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $linias = LiniaTransportMaritim::with(['ciutat', 'ports'])->get();
        
        return response()->json($linias, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $linia = new LiniaTransportMaritim();
            $linia->nom = $request->input('nom');
            $linia->ciutat_id = $request->input('ciutat_id');
            $linia->save();
            
            $ports = $request->input('ports', []);
            $linia->ports()->sync($this->formatejarPortsAmbNomLinia($ports, $linia->nom));
            
            return response()->json([
                'message' => 'Línia marítima creada exitosamente',
                'data' => $linia->load(['ciutat', 'ports'])
            ], 201);
        } catch (QueryException $e) {
            $missatge = Utilitat::errorMessage($e);
            return response()->json([
                'error' => $missatge
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LiniaTransportMaritim $liniaTransportMaritim)
    {
        $liniaTransportMaritim->load(['ciutat', 'ports']);
        
        return response()->json($liniaTransportMaritim, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LiniaTransportMaritim $liniaTransportMaritim)
    {
        try {
            $liniaTransportMaritim->nom = $request->input('nom', $liniaTransportMaritim->nom);
            $liniaTransportMaritim->ciutat_id = $request->input('ciutat_id', $liniaTransportMaritim->ciutat_id);
            $liniaTransportMaritim->save();
            
            $ports = $request->input('ports', []);
            $liniaTransportMaritim->ports()->sync($this->formatejarPortsAmbNomLinia($ports, $liniaTransportMaritim->nom));
            
            return response()->json([
                'message' => 'Línia marítima actualizada exitosamente',
                'data' => $liniaTransportMaritim->load(['ciutat', 'ports'])
            ], 200);
        } catch (QueryException $e) {
            $missatge = Utilitat::errorMessage($e);
            return response()->json([
                'error' => $missatge
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LiniaTransportMaritim $liniaTransportMaritim)
    {
        try {
            $liniaTransportMaritim->ports()->detach();
            LiniaTransportMaritim::query()->whereKey($liniaTransportMaritim->getKey())->delete();
            
            return response()->json([
                'message' => 'Línia marítima eliminada exitosamente'
            ], 200);
        } catch (QueryException $e) {
            $missatge = Utilitat::errorMessage($e);
            return response()->json([
                'error' => $missatge
            ], 400);
        }
    }

    private function formatejarPortsAmbNomLinia(array $ports, string $nomLinia): array
    {
        return collect($ports)
            ->mapWithKeys(fn ($portId) => [
                $portId => ['nom_linia_transport_maritim' => $nomLinia],
            ])
            ->all();
    }
}
