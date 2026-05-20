<?php

namespace App\Http\Controllers;

use App\Models\Incoterm;
use App\Models\Solicitud;
use App\Models\TrackingStep;
use App\Models\TipusIncoterm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Classes\Utilitat;

class IncotermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Devolver los modelos tal cual con la relación cargada.
        return Incoterm::with('tipusIncoterm')->get();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $codi = trim((string) $request->input('codi', ''));
            $nom = trim((string) $request->input('nom', ''));
            $tipusIncotermId = null;
            
            if (!empty($request->input('tipus_incoterm_id'))) {
                $tipusIncotermId = (int) $request->input('tipus_incoterm_id');
            } elseif (!empty($request->input('tipus_inconterm_id'))) {
                $tipusIncotermId = (int) $request->input('tipus_inconterm_id');
            }

            if ($tipusIncotermId !== null && ! TipusIncoterm::find($tipusIncotermId)) {
                $tipusIncotermId = null;
            }

            if ($tipusIncotermId === null && ($codi !== '' || $nom !== '')) {
                $consultaTipusIncoterm = TipusIncoterm::where('id', '>', 0);
                if ($codi !== '') {
                    $consultaTipusIncoterm->where('codi', $codi);
                }
                if ($nom !== '') {
                    $consultaTipusIncoterm->where('nom', $nom);
                }
                $tipusIncotermExistente = $consultaTipusIncoterm->first();

                $tipusIncotermId = $tipusIncotermExistente?->id;
            }

            if ($tipusIncotermId === null && $codi !== '' && $nom !== '') {
                $tipusIncoterm = TipusIncoterm::where('codi', $codi)
                    ->where('nom', $nom)
                    ->first();

                if (! $tipusIncoterm) {
                    $tipusIncoterm = new TipusIncoterm();
                    $tipusIncoterm->codi = $codi;
                    $tipusIncoterm->nom = $nom;
                    $tipusIncoterm->save();
                }

                $tipusIncotermId = $tipusIncoterm->id;
            }

            if ($tipusIncotermId === null) {
                throw new \Exception(Utilitat::errorMessage(1499), 1499);
            }

            $tipusIncoterm = TipusIncoterm::find($tipusIncotermId);
            if (! $tipusIncoterm) {
                throw new \Exception(Utilitat::errorMessage(1500), 1500);
            }

            $incoterm = new Incoterm();
            $incoterm->tipus_inconterm_id = $tipusIncotermId;
            $incoterm->tracking_steps_id = null;
            $incoterm->save();

            $defaultSteps = TrackingStep::orderBy('ordre')
                ->orderBy('id')
                ->get();

            if ($defaultSteps->isEmpty()) {
                throw new \Exception(Utilitat::errorMessage(1502), 1502);
            }

            $idsPasosPorDefecto = $defaultSteps->pluck('id')->all();
            $tipusIncoterm->trackingSteps()->syncWithoutDetaching($idsPasosPorDefecto);

            $firstStepId = $this->obtenerPrimerPasoIdParaTipoIncoterm($tipusIncoterm->id);

            if ($firstStepId === null) {
                throw new \Exception(Utilitat::errorMessage(1501), 1501);
            }

            $incoterm->tracking_steps_id = $firstStepId;
            $incoterm->save();

            DB::commit();

            return $incoterm;
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Incoterm $incoterm)
    {
        $incoterm = Incoterm::with(['tipusIncoterm', 'trackingStep'])->find($incoterm->id);
        $tipusIncoterm = $incoterm?->tipusIncoterm;

        $trackingSteps = collect();
        if ($tipusIncoterm !== null) {
            $trackingSteps = $tipusIncoterm->trackingSteps()
            ->orderBy('ordre')
            ->orderBy('id')
            ->get();

            if ($trackingSteps->isEmpty()) {
                $defaultSteps = TrackingStep::orderBy('ordre')->orderBy('id')->get();
                $idsPasosPorDefecto = $defaultSteps->pluck('id')->all();
                $tipusIncoterm->trackingSteps()->syncWithoutDetaching($idsPasosPorDefecto);
                $trackingSteps = $tipusIncoterm->trackingSteps()->orderBy('ordre')->orderBy('id')->get();
            }
        }

        $incoterm->tracking_steps = $trackingSteps;

        return $incoterm;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Incoterm $incoterm)
    {
        DB::beginTransaction();

        try {
            $incomingTipusId = null;
            if (!empty($request->input('tipus_incoterm_id'))) {
                $incomingTipusId = (int) $request->input('tipus_incoterm_id');
            } elseif (!empty($request->input('tipus_inconterm_id'))) {
                $incomingTipusId = (int) $request->input('tipus_inconterm_id');
            }

            if ($incomingTipusId === null) {
                $codigoActualizado = trim((string) $request->input('codi', ''));
                $nombreActualizado = trim((string) $request->input('nom', ''));

                if ($codigoActualizado !== '' || $nombreActualizado !== '') {
                    $consultaTipoPorTexto = TipusIncoterm::where('id', '>', 0);
                    if ($codigoActualizado !== '') {
                        $consultaTipoPorTexto->where('codi', $codigoActualizado);
                    }
                    if ($nombreActualizado !== '') {
                        $consultaTipoPorTexto->where('nom', $nombreActualizado);
                    }
                    $tipusIncotermByText = $consultaTipoPorTexto->first();

                    $incomingTipusId = $tipusIncotermByText?->id;
                }
            }

            if ($incomingTipusId !== null) {
                $tipusIncoterm = TipusIncoterm::find($incomingTipusId);
                if (! $tipusIncoterm) {
                    throw new \Exception(Utilitat::errorMessage(1500), 1500);
                }

                $firstStepId = $this->obtenerPrimerPasoIdParaTipoIncoterm($tipusIncoterm->id);

                if ($firstStepId === null) {
                    $defaultSteps = TrackingStep::orderBy('ordre')
                        ->orderBy('id')
                        ->get();

                    if ($defaultSteps->isEmpty()) {
                        throw new \Exception(Utilitat::errorMessage(1502), 1502);
                    }

                    $idsPasosPorDefecto = $defaultSteps->pluck('id')->all();
                    $tipusIncoterm->trackingSteps()->syncWithoutDetaching($idsPasosPorDefecto);

                    $firstStepId = $this->obtenerPrimerPasoIdParaTipoIncoterm($tipusIncoterm->id);
                }

                if ($firstStepId === null) {
                    throw new \Exception(Utilitat::errorMessage(1501), 1501);
                }

                $incoterm->tipus_inconterm_id = $tipusIncoterm->id;
                $incoterm->tracking_steps_id = $firstStepId;
            }

            $incoterm->save();

            DB::commit();

            return $incoterm;
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incoterm $incoterm)
    {
        DB::beginTransaction();

        try {
            $tieneSolicitudes = Solicitud::where('incoterm_id', $incoterm->id)->exists();

            if ($tieneSolicitudes) {
                throw new \Exception(Utilitat::errorMessage(1494), 1494);
            }

            $tipusIncoterm = $incoterm->tipusIncoterm;
            $incoterm->delete();

            if ($tipusIncoterm) {
                $tipusIncoterm->delete();
            }

            DB::commit();

            return ['message' => 'Incoterm eliminado correctamente.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return response(Utilitat::errorMessage($e), 500);
        }
    }

    private function obtenerPrimerPasoIdParaTipoIncoterm(int $tipusIncotermId): ?int
    {
        $tipusIncoterm = TipusIncoterm::find($tipusIncotermId);
        if (! $tipusIncoterm) {
            return null;
        }

        $firstStep = $tipusIncoterm->trackingSteps()->orderBy('ordre')->orderBy('id')->first();

        return $firstStep?->id;
    }

}
