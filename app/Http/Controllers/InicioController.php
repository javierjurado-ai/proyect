<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InicioController extends Controller
{
    public function mostrarPaginaInicio()
    {
        return view('panel.inicio');
    }
}
