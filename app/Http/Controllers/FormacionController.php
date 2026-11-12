<?php

namespace App\Http\Controllers;

use App\Models\Enums\Modalidad;
use App\Models\Formacion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormacionController extends Controller
{
    // Listar todas las formaciones
    public function listar()
    {
        return response()->json(Formacion::all());
    }

    // Mostrar una formación concreta
    public function mostrar($id)
    {
        $formacion = Formacion::find($id);

        if (! $formacion) {
            return response()->json(['mensaje' => 'Formación no encontrada'], 404);
        }

        return response()->json($formacion);
    }

    // Crear una nueva formación
    public function crear(Request $request)
    {
        $datos = $request->validate([
            'nombre'        => 'required|string|max:100',
            'codigo_accion' => 'nullable|string|max:45',
            'modalidad'     => ['nullable', Rule::enum(Modalidad::class)],
            'sector'        => 'nullable|string|max:60',
            'horas_totales' => 'nullable|integer|min:0',
            'activo'        => 'nullable|boolean',
        ]);

        $formacion = Formacion::create($datos);

        return response()->json($formacion, 201);
    }

    // Actualizar una formación
    public function actualizar(Request $request, $id)
    {
        $formacion = Formacion::find($id);

        if (! $formacion) {
            return response()->json(['mensaje' => 'Formación no encontrada'], 404);
        }

        $datos = $request->validate([
            'nombre'        => 'required|string|max:100',
            'codigo_accion' => 'nullable|string|max:45',
            'modalidad'     => ['nullable', Rule::enum(Modalidad::class)],
            'sector'        => 'nullable|string|max:60',
            'horas_totales' => 'nullable|integer|min:0',
            'activo'        => 'nullable|boolean',
        ]);

        $formacion->update($datos);

        return response()->json($formacion);
    }

    // Eliminar una formación
    public function eliminar($id)
    {
        $formacion = Formacion::find($id);

        if (! $formacion) {
            return response()->json(['mensaje' => 'Formación no encontrada'], 404);
        }

        try {
            $formacion->delete();
            return response()->json(['mensaje' => 'Formación eliminada']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: la formación tiene cursos asociados',
            ], 409);
        }
    }
}