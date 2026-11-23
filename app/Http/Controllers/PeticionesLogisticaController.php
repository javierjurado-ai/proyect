<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeticionesLogistica;
use App\Models\Enums\Estado;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class PeticionesLogisticaController extends Controller
{

    public function index(Request $request){
        $personaId = Auth::user()->persona_id;
        $rol     = Rol::where('persona_id', $personaId)->value('rol')?->value;
        $personas = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);
        $nombre = collect([$personas->nombre, $personas->primer_apellido, $personas->segundo_apellido])
            ->filter()
            ->implode(' ');

        if (! $rol || ! $personas) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['usuario' => 'No tienes acceso a la página.']);
        }
        $logistica = PeticionesLogistica::all();

        return view('logistica.index', compact('logistica', 'rol', 'nombre','personas'));
    }

    public function listarPeticionesLogistica()
    {
        return response()->json(PeticionesLogistica::all());
    }
    public function obtenerPeticionLogisticaPorID($id)
    {
        $peticionesLogistica = PeticionesLogistica::findOrFail($id);

        return response()->json($peticionesLogistica, 200);
    }
    public function agregarPeticionLogistica(Request $request)
    {
        $datos = $request->validate([
            'fecha'         => ['required', 'date'],
            'estado'        => ['required', Rule::enum(Estado::class)],
            'observaciones' => ['nullable', 'string', 'max:200'],
        ]);

        return response()->json(PeticionesLogistica::create($datos), 201);
    }

    public function actualizarPeticionLogistica($id, Request $request)
    {
        $comunicacion = PeticionesLogistica::findOrFail($id);

        $datos = $request->validate([
            'fecha'         => ['required', 'date'],
            'estado'        => ['required', Rule::enum(Estado::class)],
            'observaciones' => ['nullable', 'string', 'max:200'],
        ]);


        $comunicacion->update($datos);

        return response()->json($comunicacion, 200);
    }

    public function eliminarPeticionLogistica($id)
    {
        $comunicaciones = PeticionesLogistica::findOrFail($id);

        try {
            $comunicaciones->delete();
            return response()->json(['mensaje' => 'peticion logistica eliminada'], 200);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: tiene productos o cursos asociados',
            ], 409);
        }
    }
}