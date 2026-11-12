<?php

use Illuminate\Support\Facades\Route;
use App\Models\PeticionesLogistica;
use App\Http\Controllers\CentroController;
use App\Http\Controllers\ComunicacionFundaeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormacionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\CreditoFormativoController;


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
Route::post('/comunicacionFundae/crear', [ComunicacionFundaeController::class, 'agregarComunicacionFundae']);
Route::put('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'actualizarComunicacionFundae']);
Route::delete('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'eliminarComunicacionFundae']);

// Rutas para el controlador de empresas
Route::get('/empresa/listar', [EmpresaController::class, 'listarEmpresa']);
Route::get('/empresa/{empresa}', [EmpresaController::class, 'optenerEmpresaPorID']);
Route::post('/empresa', [EmpresaController::class, 'agregarEmpresa']);
Route::put('/empresa/{empresa}', [EmpresaController::class, 'actualizarEmpresa']);
Route::delete('/empresa/{empresa}', [EmpresaController::class, 'eliminarEmpresa']);

// Rutas para el controlador de formaciones
Route::get('/formacion', [FormacionController::class, 'listarFormacion']);
Route::get('/formacion/{id}', [FormacionController::class, 'mostrarFormacion']);
Route::post('/formacion', [FormacionController::class, 'crearFormacion']);
Route::put('/formacion/{id}', [FormacionController::class, 'actualizarFormacion']);
Route::delete('/formacin/{id}', [FormacionController::class, 'eliminarFormacion']);

// Rutas para el controlador de centro
Route::get('/centro', [CentroController::class, 'listarCentro']);
Route::get('/centro/{id}', [CentroController::class, 'mostrarCentro']);
Route::post('/centro', [CentroController::class, 'crearCentro']);
Route::put('/centro/{id}', [CentroController::class, 'actualizarCentro']);
Route::delete('/centro/{id}', [CentroController::class, 'eliminarCentro']);


// Rutas para el controlador de créditos formativos
Route::get('/credito', [CreditoFormativoController::class, 'listarCredito']);
Route::get('/credito/{id}', [CreditoFormativoController::class, 'mostrarCredito']);
Route::post('/credito', [CreditoFormativoController::class, 'crearCredito']);
Route::put('/credito/{id}', [CreditoFormativoController::class, 'actualizarCredito']);
Route::delete('/credito/{id}', [CreditoFormativoController::class, 'eliminarcredito']);