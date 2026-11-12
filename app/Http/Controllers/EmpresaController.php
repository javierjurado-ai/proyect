<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    // Listar todas las empresas
    public function listarEmpresa()
    {
        return response()->json(Empresa::all());
    }

    // Mostrar una empresa en concreto
    public function optenerEmpresaPorID($id)
    {
        $empresa = Empresa::find($id);

        if (! $empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada'], 404);
        }

        return response()->json($empresa);
    }

    // Crear una empresa
    public function agregarEmpresa(Request $request)
    {
        $datos = $request->validate([
            'cif'                => 'required|string|max:15|unique:empresa,cif',
            'nombre'             => 'required|string|max:100',
            'total_trabajadores' => 'nullable|integer|min:0',
        ]);

        $empresa = Empresa::create($datos);

        return response()->json($empresa, 201);
    }

    // Actualizar una empresa
    public function actualizarEmpresa(Request $request, $id)
    {
        $empresa = Empresa::find($id);

        if (! $empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada'], 404);
        }

        $datos = $request->validate([
            'cif'                => ['required', 'string', 'max:15', Rule::unique('empresa', 'cif')->ignore($empresa->id)],
            'nombre'             => 'required|string|max:100',
            'total_trabajadores' => 'nullable|integer|min:0',
        ]);

        $empresa->update($datos);

        return response()->json($empresa);
    }

    // Eliminar una empresa
    public function eliminarEmpresa($id)
    {
        $empresa = Empresa::find($id);

        if (! $empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada'], 404);
        }

        try {
            $empresa->delete();
            return response()->json(['mensaje' => 'Empresa eliminada']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: la empresa tiene datos relacionados',
            ], 409);
        }
    }
}