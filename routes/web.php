<?php

use App\Http\Controllers\EmpresaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/empresas', [EmpresaController::class, 'listar']);
Route::get('/empresas/{empresa}', [EmpresaController::class, 'mostrar']);
Route::post('/empresas', [EmpresaController::class, 'crear']);
Route::put('/empresas/{empresa}', [EmpresaController::class, 'actualizar']);
Route::delete('/empresas/{empresa}', [EmpresaController::class, 'eliminar']);