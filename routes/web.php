<?php

use App\Http\Controllers\EmpresaController;
use Illuminate\Support\Facades\Route;
use App\Models\PeticionesLogistica;
use App\Http\Controllers\PersonaController;

Route::get('/persona/listar', [PersonaController::class, 'listarPersonas']);
Route::get('/persona/{id}', [PersonaController::class, 'optenerUsuarioPorID']);
Route::post('/persona', [PersonaController::class, 'agregarPersona']);
Route::put('/persona/{id}', [PersonaController::class, 'actualizarPersona']);
Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

Route::get('/empresas', [EmpresaController::class, 'listar']);
Route::get('/empresas/{empresa}', [EmpresaController::class, 'mostrar']);
Route::post('/empresas', [EmpresaController::class, 'crear']);
Route::put('/empresas/{empresa}', [EmpresaController::class, 'actualizar']);
Route::delete('/empresas/{empresa}', [EmpresaController::class, 'eliminar']);

Route::get('/', function () {
    return view('welcome');
});

