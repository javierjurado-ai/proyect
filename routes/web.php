<?php

use Illuminate\Support\Facades\Route;
use App\Models\PeticionesLogistica;
use App\Http\Controllers\CentroController;
use App\Http\Controllers\ComunicacionFundaeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormacionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\PeticionesLogisticaController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/probar-logistica', function () {
    return PeticionesLogistica::all();
});

Route::resource('cursos', CursoController::class);

// Rutas para el controlador de personas
Route::get('/persona/listar', [PersonaController::class, 'listarPersonas']);
Route::get('/persona/{id}', [PersonaController::class, 'optenerUsuarioPorID']);
Route::post('/persona', [PersonaController::class, 'agregarPersona']);
Route::put('/persona/{id}', [PersonaController::class, 'actualizarPersona']);
Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

// Rutas para el controlador de comunicaciones FUNDAE
Route::get('/comunicacionFundae/listar', [ComunicacionFundaeController::class, 'listarComunicacionesFundae']);
Route::get('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'optenerComunicacionFundaePorID']);
Route::post('/comunicacionFundae', [ComunicacionFundaeController::class, 'agregarComunicacionFundae']);
Route::put('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'actualizarComunicacionFundae']);
Route::delete('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'eliminarComunicacionFundae']);

// Rutas para el controlador de empresas
Route::get('/empresa/listar', [EmpresaController::class, 'listarEmpresa']);
Route::get('/empresa/{empresa}', [EmpresaController::class, 'optenerEmpresaPorID']);
Route::post('/empresa', [EmpresaController::class, 'agregarEmpresa']);
Route::put('/empresa/{empresa}', [EmpresaController::class, 'actualizarEmpresa']);
Route::delete('/empresa/{empresa}', [EmpresaController::class, 'eliminarEmpresa']);

// Rutas para el controlador de formaciones
Route::get('/formaciones', [FormacionController::class, 'listar']);
Route::get('/formaciones/{id}', [FormacionController::class, 'mostrar']);
Route::post('/formaciones', [FormacionController::class, 'crear']);
Route::put('/formaciones/{id}', [FormacionController::class, 'actualizar']);
Route::delete('/formaciones/{id}', [FormacionController::class, 'eliminar']);

// Rutas para el controlador de centro
Route::get('/centro', [CentroController::class, 'listarCentro']);
Route::get('/centro/{id}', [CentroController::class, 'optenerCentroPorID']);
Route::post('/centro', [CentroController::class, 'agregarCentro']);
Route::put('/centro/{id}', [CentroController::class, 'actualizarCentro']);
Route::delete('/centro/{id}', [CentroController::class, 'eliminarCentro']);

// Rutas para el controlador de peticiones logistica
Route::get('/peticionesLogistica/listar', [PeticionesLogisticaController::class, 'listarPeticionesLogistica']);
Route::get('/peticionesLogistica/{id}', [PeticionesLogisticaController::class, 'optenerPeticionLogisticaPorID']);
Route::post('/peticionesLogistica', [PeticionesLogisticaController::class, 'agregarPeticionLogistica']);
Route::put('/peticionesLogistica/{id}', [PeticionesLogisticaController::class, 'actualizarPeticionLogistica']);
Route::delete('/peticionesLogistica/{id}', [PeticionesLogisticaController::class, 'eliminarPeticionLogistica']);

// Rutas para el controlador de centro
Route::get('/curso/listar', [CursoController::class, 'listarCurso']);
Route::get('/curso/{id}', [CursoController::class, 'optenerCursoPorID']);
Route::post('/curso', [CursoController::class, 'agregarCurso']);
Route::put('/curso/{id}', [CursoController::class, 'actualizarCurso']);
Route::delete('/curso/{id}', [CursoController::class, 'eliminarCurso']);