<?php

use App\Http\Controllers\AuthentificationController;
use App\Http\Controllers\UsuariController;
use App\Http\Controllers\IncotermController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\CiutatController;
use App\Http\Controllers\TipusContenidorController;
use App\Http\Controllers\TipusCarregaController;
use App\Http\Controllers\TipusIncotermController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    $usuari = $request->user();

    if (!$usuari) {
        return response()->json(['error' => 'No autenticado'], 401);
    }

    $usuari->load('rol');

    return $usuari;
})->middleware('auth:sanctum');

Route::post('/login', [AuthentificationController::class, 'login']);



Route::middleware(['auth:sanctum'])->group(function () {

Route::get('/usuaris', [UsuariController::class, 'index']);

Route::get('/ciutats', [CiutatController::class, 'index']);
Route::get('/incoterms', [IncotermController::class, 'index']);
Route::get('/ofertes', [OfertaController::class, 'index']);
Route::get('/ofertes/{id}', [OfertaController::class, 'show']);
Route::put('/ofertes/{oferta}', [OfertaController::class, 'update']);

Route::get('/tipos-contenedor', [TipusContenidorController::class, 'index']);

Route::get('/tipos-carga', [TipusCarregaController::class, 'index']);

Route::get('/tipos-incoterm', [TipusIncotermController::class, 'index']);

Route::get('/getOperadores-logisticos', [UsuariController::class, 'getOperadoresLogisticos']);

Route::post('/solicitud-oferta', [SolicitudController::class, 'store']);

Route::post('/usuaris', [UsuariController::class, 'store']);
Route::delete('/usuaris/{usuari}', [UsuariController::class, 'destroy']);
});
