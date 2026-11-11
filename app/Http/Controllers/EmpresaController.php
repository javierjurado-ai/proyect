<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    // Listar todas las empresas
    public function listar()
    {
        return response()->json(Empresa::all());
    }

    // Mostrar una empresa en concreto
    public function mostrar(Empresa $empresa)
    {
        return response()->json($empresa);
    }

    // Crear una empresa
    public function crear(Request $request)
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
    public function actualizar(Request $request, Empresa $empresa)
    {
        $datos = $request->validate([
            'cif'                => ['required', 'string', 'max:15', Rule::unique('empresa', 'cif')->ignore($empresa->id)],
            'nombre'             => 'required|string|max:100',
            'total_trabajadores' => 'nullable|integer|min:0',
        ]);

        $empresa->update($datos);

        return response()->json($empresa);
    }

    // Eliminar una empresa
    public function eliminar(Empresa $empresa)
    {
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
