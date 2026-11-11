<?php

use Illuminate\Support\Facades\Route;
use App\Models\PeticionesLogistica;
use App\Http\Controllers\PersonaController;

Route::get('/persona/listar', [PersonaController::class, 'listarPersonas']);
Route::get('/persona/{id}', [PersonaController::class, 'optenerUsuarioPorID']);
Route::post('/persona', [PersonaController::class, 'agregarPersona']);
Route::put('/persona/{id}', [PersonaController::class, 'actualizarPersona']);
Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/probar-logistica', function () {
    return PeticionesLogistica::all();
});
