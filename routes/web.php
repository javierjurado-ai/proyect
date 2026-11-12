<?php

use App\Http\Controllers\CentroController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormacionController;
use App\Http\Controllers\PersonaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para el controlador de personas
Route::get('/persona/listar', [PersonaController::class, 'listarPersonas']);
Route::get('/persona/{id}', [PersonaController::class, 'optenerUsuarioPorID']);
Route::post('/persona', [PersonaController::class, 'agregarPersona']);
Route::put('/persona/{id}', [PersonaController::class, 'actualizarPersona']);
Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

// Rutas para el controlador de empresas
Route::get('/empresas', [EmpresaController::class, 'listar']);
Route::get('/empresas/{id}', [EmpresaController::class, 'mostrar']);
Route::post('/empresas', [EmpresaController::class, 'crear']);
Route::put('/empresas/{id}', [EmpresaController::class, 'actualizar']);
Route::delete('/empresas/{id}', [EmpresaController::class, 'eliminar']);

// Rutas para el controlador de formaciones
Route::get('/formaciones', [FormacionController::class, 'listar']);
Route::get('/formaciones/{id}', [FormacionController::class, 'mostrar']);
Route::post('/formaciones', [FormacionController::class, 'crear']);
Route::put('/formaciones/{id}', [FormacionController::class, 'actualizar']);
Route::delete('/formaciones/{id}', [FormacionController::class, 'eliminar']);

// Rutas para el controlador de centros
Route::get('/centros', [CentroController::class, 'listar']);
Route::get('/centros/{id}', [CentroController::class, 'mostrar']);
Route::post('/centros', [CentroController::class, 'crear']);
Route::put('/centros/{id}', [CentroController::class, 'actualizar']);
Route::delete('/centros/{id}', [CentroController::class, 'eliminar']);