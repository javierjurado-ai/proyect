<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class InicioController extends Controller
{
    public function mostrarPaginaInicio(Request $request)
{
    $personaId = Auth::user()->persona_id;

    $rol     = Rol::where('persona_id', $personaId)->value('rol')?->value;
    $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

    if (! $rol || ! $persona) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['usuario' => 'No tienes acceso a la página.']);
    }

    $nombre = collect([$persona->nombre, $persona->primer_apellido, $persona->segundo_apellido])
        ->filter()
        ->implode(' ');

    return view('panel.inicio', compact('rol', 'nombre'));
}
}
