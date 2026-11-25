<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\AuthController;

Route::post('/persona/crearUsuario', [AuthController::class, 'crearUsuario'])
    ->withoutMiddleware([\Illuminate\Auth\Middleware\Authenticate::class, 'auth', 'auth:sanctum']);



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


