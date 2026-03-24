<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuari;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */


    public function run(): void
    {
        // User::factory(10)->create();
        $adminRol = Rol::firstOrCreate([
            'rol' => 'Admin',
        ]);

        Rol::firstOrCreate([
            'rol' => 'Operador',
        ]);

        Rol::firstOrCreate([
            'rol' => 'Client',
        ]);

        Usuari::updateOrCreate([
            'correu' => 'admin@multimar.com',
        ], [
            'contrasenya' => '123123',
            'nom' => 'Admin',
            'cognoms' => 'Admin',
            'rol_id' => $adminRol->id,
            'dni' => '65566565B',
        ]);
    }
}
