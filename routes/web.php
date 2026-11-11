<?php

use Illuminate\Support\Facades\Route;
use App\Models\PeticionesLogistica;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/probar-logistica', function () {
    return PeticionesLogistica::all();
});
