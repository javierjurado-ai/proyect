<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use App\Models\Enums\Genero;
use Illuminate\Validation\Rule;
use App\Models\enums\LOPD;
class PersonaController extends Controller
{
    public function listarPersonas()
    {
        return response()->json(Persona::all());
    }
    public function obtenerUsuarioPorID($id)
    {
        $persona = Persona::findOrFail($id);

        ($persona->toArray());
    }
    public function agregarPersona(Request $request)
    {
        $datos = $request->validate([
            'empresa_id' => 'required|exists:empresa,id',
            'nombre' => 'required|string|max:45',
            'primer_apellido' => 'required|string|max:45',
            'segundo_apellido' => 'required|string|max:45',
            'fecha_nacimiento' => 'required|date',
            'genero' => ['required', Rule::enum(Genero::class)],
            'nacionalidad' => 'required|string|max:45',
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:150',
            'email' => 'required|string|max:100',
            'DNI' => 'nullable|string|max:15',
            'NIE' => 'nullable|string|max:15',
            'numero_seguridad_social' => 'nullable|string|max:20',
            'LOPD' => ['required', Rule::enum(LOPD::class)],
        ]);

        $persona = Persona::create($datos);

        return response()->json($persona, 201);
    }
    public function actualizarPersona(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);

        $datos = $request->validate([
            'empresa_id' => 'required|exists:empresa,id',
            'nombre' => 'required|string|max:45',
            'primer_apellido' => 'required|string|max:45',
            'segundo_apellido' => 'required|string|max:45',
            'fecha_nacimiento' => 'required|date',
            'genero' => ['required', Rule::enum(Genero::class)],
            'nacionalidad' => 'required|string|max:45',
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:150',
            'email' => 'required|string|max:100',
            'DNI' => 'nullable|string|max:15',
            'NIE' => 'nullable|string|max:15',
            'numero_seguridad_social' => 'nullable|string|max:20',
            'LOPD' => ['required', Rule::enum(LOPD::class)],
        ]);

        $persona->update($datos);

        return response()->json($persona, 200);
    }
    public function eliminarPersona($id)
    {
        $persona = Persona::findOrFail($id);

        $persona->delete();

        return response()->json(['mensaje' => 'Persona eliminada'], 200);
    }
}
