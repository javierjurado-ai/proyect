<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CentroController extends Controller
{
    // Listar todos los centros
    public function listarCentro()
    {
        return response()->json(Centro::all());
    }

    // Mostrar un centro por id
    public function optenerCentroPorID($id)
    {
        $centro = Centro::findOrFail($id);

        return response()->json($centro,201);
    }

    // Crear un centro
    public function agregarCentro(Request $request)
    {
        $datos = $request->validate($this->reglas());

        $centro = Centro::create($datos);

        return response()->json($centro, 201);
    }

    // Actualizar un centro
    public function actualizarCentro(Request $request, $id)
    {
        $centro = Centro::find($id);

        if (! $centro) {
            return response()->json(['mensaje' => 'Centro no encontrado'], 404);
        }

        $datos = $request->validate($this->reglas());

        $centro->update($datos);

        return response()->json($centro);
    }

    // Eliminar un centro
    public function eliminarCentro($id)
    {
        $centro = Centro::find($id);

        if (! $centro) {
            return response()->json(['mensaje' => 'Centro no encontrado'], 404);
        }

        try {
            $centro->delete();
            return response()->json(['mensaje' => 'Centro eliminado']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: el centro tiene cursos asociados',
            ], 409);
        }
    }

    // Reglas de validación comunes a crear y actualizar
    private function reglas(): array
    {
        return [
            'empresa_id'  => 'nullable|integer|exists:empresa,id',
            'nombre'      => 'required|string|max:45',
            'responsable' => 'nullable|string|max:45',
            'direccion'   => 'nullable|string|max:45',
            'telefono'    => 'nullable|string|max:45',
            'email'       => 'nullable|email|max:45',
        ];
    }
}