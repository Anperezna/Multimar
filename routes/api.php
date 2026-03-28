<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuariController;
use App\Http\Controllers\IncotermController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\TipusContenidorController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/usuaris', [UsuariController::class, 'index']);
Route::get('/incoterms', [IncotermController::class, 'index']);
Route::get('/ofertes', [OfertaController::class, 'index']);

Route::get('/tipos-contenedor', [App\Http\Controllers\TipusContenidorController::class, 'index']);

