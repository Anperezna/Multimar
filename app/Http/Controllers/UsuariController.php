<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use App\Models\Rol;
use App\Models\Usuari;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuariController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Usuari::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'creador_id' => ['required', 'integer', 'exists:usuaris,id'],
            'nombre' => ['required', 'string', 'max:50'],
            'apellidos' => ['required', 'string', 'max:50'],
            'dni' => ['nullable', 'string', 'max:50'],
            'pais' => ['required', 'string', 'max:50'],
            'correo' => ['required', 'email', 'max:50', 'unique:usuaris,correu'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'rol' => ['nullable', 'string', 'max:50'],
        ]);

        $creador = Usuari::query()->with('rol')->find((int) $request->input('creador_id'));

        if (! $creador || ! $creador->rol) {
            return response()->json([
                'message' => 'No se pudo resolver el rol del usuario creador.',
            ], 422);
        }

        $rolCreador = mb_strtolower(trim((string) $creador->rol->rol));

        if (! in_array($rolCreador, ['admin', 'operador'], true)) {
            return response()->json([
                'message' => 'El usuario creador no tiene permisos para crear usuarios.',
            ], 403);
        }

        $rolInput = $rolCreador === 'operador'
            ? 'usuari'
            : trim((string) $request->input('rol', 'usuari'));
    
        $rol = is_numeric($rolInput)
            ? Rol::query()->find((int) $rolInput)
            : Rol::query()->whereRaw('LOWER(rol) = ?', [mb_strtolower($rolInput)])->first();

        if (! $rol) {
            return response()->json([
                'message' => 'El rol indicado no existe.',
            ], 422);
        }

        $pais = Pais::query()->firstOrCreate([
            'nom' => trim((string) $request->input('pais')),
        ]);

        $usuari = new Usuari();
        $usuari->nom = $request->input('nombre');
        $usuari->cognoms = $request->input('apellidos');
        $usuari->dni = $request->input('dni');
        $usuari->correu = $request->input('correo');
        $usuari->contrasenya = Hash::make($request->input('password'));
        $usuari->rol_id = $rol->id;
        $usuari->pais_id = $pais->id;
        $usuari->empresa = null;
        $usuari->foto_user = '';
        $usuari->foto_dni_front = '';
        $usuari->foto_dni_back = '';
        $usuari->usuari_id = $creador->id;
        
        try {
            $usuari->save();

            $response = [
                'id' => $usuari->id,
                'nombre' => $usuari->nom,
                'apellidos' => $usuari->cognoms,
                'dni' => $usuari->dni,
                'correo' => $usuari->correu,
                'pais' => $pais->nom,
                'rol' => $rol->rol,
            ];

            return response()->json($response, 201);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error creating usuari', 'error' => $e->getMessage()], 500);
        }
    
    
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

    public function getOperadoresLogisticos()
    {
        $operadoresLogisticos = Usuari::where('rol_id', 2)->get();
        return response()->json($operadoresLogisticos);
    }
}
