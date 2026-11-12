<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormacionController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\ComunicacionFundaeController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para el controlador de personas
Route::get('/persona/listar', [PersonaController::class, 'listarPersonas']);
Route::get('/persona/{id}', [PersonaController::class, 'optenerUsuarioPorID']);
Route::post('/persona', [PersonaController::class, 'agregarPersona']);
Route::put('/persona/{id}', [PersonaController::class, 'actualizarPersona']);
Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

Route::get('/comunicacionFundae/listar', [ComunicacionFundaeController::class, 'listarComunicacionesFundae']);
Route::get('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'optenerComunicacionFundaePorID']);
Route::post('/comunicacionFundae/crear', [ComunicacionFundaeController::class, 'agregarComunicacionFundae']);
Route::put('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'actualizarComunicacionFundae']);
Route::delete('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'eliminarComunicacionFundae']);

Route::get('/empresa/listar', [EmpresaController::class, 'listarEmpresa']);
Route::get('/empresa/{empresa}', [EmpresaController::class, 'optenerEmpresaPorID']);
Route::post('/empresa', [EmpresaController::class, 'agregarEmpresa']);
Route::put('/empresa/{empresa}', [EmpresaController::class, 'actualizarEmpresa']);
Route::delete('/empresa/{empresa}', [EmpresaController::class, 'eliminarEmpresa']);
// Rutas para el controlador de empresas

// Rutas para el controlador de formaciones
Route::get('/formaciones', [FormacionController::class, 'listar']);
Route::get('/formaciones/{formacion}', [FormacionController::class, 'mostrar']);
Route::post('/formaciones', [FormacionController::class, 'crear']);
Route::put('/formaciones/{formacion}', [FormacionController::class, 'actualizar']);
Route::delete('/formaciones/{formacion}', [FormacionController::class, 'eliminar']);