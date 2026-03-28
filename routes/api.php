<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuariController;
use App\Http\Controllers\IncotermController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\TipusContenidorController;
use App\Http\Controllers\TipusCarregaController;
use App\Http\Controllers\TipusIncotermController;
use App\Http\Controllers\SolicitudController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/usuaris', [UsuariController::class, 'index']);
Route::get('/incoterms', [IncotermController::class, 'index']);
Route::get('/ofertes', [OfertaController::class, 'index']);

Route::get('/tipos-contenedor', [App\Http\Controllers\TipusContenidorController::class, 'index']);

Route::get('/tipos-carga', [App\Http\Controllers\TipusCarregaController::class, 'index']);

Route::get('/tipos-incoterm', [App\Http\Controllers\TipusIncotermController::class, 'index']);

Route::get('/getOperadores-logisticos', [App\Http\Controllers\UsuariController::class, 'getOperadoresLogisticos']);

Route::post('/solicitud-oferta', [App\Http\Controllers\SolicitudController::class, 'store']);

