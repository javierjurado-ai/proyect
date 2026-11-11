<?php

use Illuminate\Support\Facades\Route;
use App\Models\Curso;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/probar-curso', function () {
    return Curso::all();
});
