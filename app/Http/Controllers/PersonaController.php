<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use App\Models\Enums\Genero;
use Illuminate\Validation\Rule;

class PersonaController extends Controller
{
    public function listarPersonas()
    {
        $personas = Persona::all();

        dd($personas->toArray());
    }
    public function optenerUsuarioPorID($id)
    {
        $persona = Persona::findOrFail($id);

        dd($persona->toArray());
    }
    public function agregarPersona(Request $request)
    {
        $datos = $request->validate([
            'empresa_id' => 'required|exists:empresa,id',
            'nombre' => 'required|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'genero' => ['required', Rule::enum(Genero::class)],
            'nacionalidad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'email' => 'required|email|unique:persona,email',
            'DNI' => 'nullable|string|max:9|unique:persona,DNI',
            'NIE' => 'nullable|string|max:9',
            'numero_seguridad_social' => 'nullable|string|max:20',
            'LOPD' => 'required|boolean',
        ]);

        $persona = Persona::create($datos);

        return response()->json($persona, 201);
    }
    public function actualizarPersona(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);

        $datos = $request->validate([
            'empresa_id' => 'required|exists:empresa,id',
            'nombre' => 'required|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'genero' => ['required', Rule::enum(Genero::class)],
            'nacionalidad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('persona', 'email')->ignore($persona->idusuario, 'idusuario'),
            ],
            'DNI' => [
                'nullable',
                'string',
                'max:9',
                Rule::unique('persona', 'DNI')->ignore($persona->idusuario, 'idusuario'),
            ],
            'NIE' => 'nullable|string|max:9',
            'numero_seguridad_social' => 'nullable|string|max:20',
            'LOPD' => 'required|boolean',
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
