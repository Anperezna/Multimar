<?php

namespace App\Http\Controllers;

use App\Models\Usuari;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthentificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        $usuari = Usuari::query()->where('correu', $request->input('correu'))->first();

        if($usuari && Hash::check($request->input('password'), $usuari->contrasenya)) {
            $token = $usuari->createToken('auth_token')->plainTextToken;
            return response()->json(['token' => $token], 200);
        
            } else {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Usuari $usuari)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usuari $usuari)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usuari $usuari)
    {
        //
    }
}
