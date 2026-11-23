<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeticionesLogistica;
use App\Models\Enums\Estado;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class PeticionesLogisticaController extends Controller
{
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