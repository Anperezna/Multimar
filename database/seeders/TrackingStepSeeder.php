<?php

namespace Database\Seeders;

use App\Models\Incoterm;
use App\Models\TrackingStep;
use Illuminate\Database\Seeder;

class TrackingStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los incoterms
        $incoterms = Incoterm::all();

        if ($incoterms->isEmpty()) {
            $this->command->info('No hay incoterms en la base de datos. Crea algunos primero.');
            return;
        }

        // Pasos estándar de seguimiento que se aplican a todos los incoterms
        $pasosGenericos = [
            ['ordre' => 1, 'nom' => 'Reserva de espacio'],
            ['ordre' => 2, 'nom' => 'Preparación de documentos'],
            ['ordre' => 3, 'nom' => 'Entrega de mercancía al puerto'],
            ['ordre' => 4, 'nom' => 'Carga en contenedor'],
            ['ordre' => 5, 'nom' => 'Zarpe del buque'],
            ['ordre' => 6, 'nom' => 'Transporte marítimo'],
            ['ordre' => 7, 'nom' => 'Llegada a puerto destino'],
            ['ordre' => 8, 'nom' => 'Descarga del buque'],
            ['ordre' => 9, 'nom' => 'Aduanas destino'],
            ['ordre' => 10, 'nom' => 'Entrega a cliente final'],
        ];

        // Crear los pasos para cada incoterm
        foreach ($incoterms as $incoterm) {
            foreach ($pasosGenericos as $pasoData) {
                // Verificar si el paso ya existe para evitar duplicados
                $exists = TrackingStep::where('incoterm_id', $incoterm->id)
                    ->where('ordre', $pasoData['ordre'])
                    ->exists();

                if (!$exists) {
                    TrackingStep::create([
                        'incoterm_id' => $incoterm->id,
                        'ordre' => $pasoData['ordre'],
                        'nom' => $pasoData['nom'],
                        'activo' => true,
                    ]);
                }
            }
        }

        $this->command->info('Pasos de seguimiento creados exitosamente para todos los incoterms.');
    }
}
