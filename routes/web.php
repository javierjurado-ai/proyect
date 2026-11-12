<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormacionController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Rutas para el controlador de empresas
Route::get('/empresas', [EmpresaController::class, 'listar']);
Route::get('/empresas/{empresa}', [EmpresaController::class, 'mostrar']);
Route::post('/empresas', [EmpresaController::class, 'crear']);
Route::put('/empresas/{empresa}', [EmpresaController::class, 'actualizar']);
Route::delete('/empresas/{empresa}', [EmpresaController::class, 'eliminar']);


// Rutas para el controlador de formaciones
Route::get('/formaciones', [FormacionController::class, 'listar']);
Route::get('/formaciones/{formacion}', [FormacionController::class, 'mostrar']);
Route::post('/formaciones', [FormacionController::class, 'crear']);
Route::put('/formaciones/{formacion}', [FormacionController::class, 'actualizar']);
Route::delete('/formaciones/{formacion}', [FormacionController::class, 'eliminar']);