<?php

namespace App\Http\Controllers;

use App\Models\CreditoFormativo;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CreditoFormativoController extends Controller
{
    // Listar todos los créditos formativos
    public function listarCredito()
    {
        return response()->json(CreditoFormativo::all());
    }

    // Mostrar un crédito formativo por id
    public function mostrarCredito($id)
    {
        $credito = CreditoFormativo::find($id);

        if (! $credito) {
            return response()->json(['mensaje' => 'Crédito formativo no encontrado'], 404);
        }

        return response()->json($credito);
    }

    // Crear un crédito formativo
    public function crearCredito(Request $request)
    {
        $datos = $request->validate($this->reglas());

        $credito = CreditoFormativo::create($datos);

        return response()->json($credito, 201);
    }

    // Actualizar un crédito formativo
    public function actualizarCredito(Request $request, $id)
    {
        $credito = CreditoFormativo::find($id);

        if (! $credito) {
            return response()->json(['mensaje' => 'Crédito formativo no encontrado'], 404);
        }

        $datos = $request->validate($this->reglas());

        $credito->update($datos);

        return response()->json($credito);
    }

    // Eliminar un crédito formativo
    public function eliminarCredito($id)
    {
        $credito = CreditoFormativo::find($id);

        if (! $credito) {
            return response()->json(['mensaje' => 'Crédito formativo no encontrado'], 404);
        }

        try {
            $credito->delete();
            return response()->json(['mensaje' => 'Crédito formativo eliminado']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: el crédito formativo tiene datos relacionados',
            ], 409);
        }
    }

    // Reglas de validación comunes a crear y actualizar
    private function reglas(): array
    {
        return [
            'empresa_id'        => 'required|integer|exists:empresa,id',
            'año'               => 'required|integer|between:2000,2100',
            'importe_total'     => 'required|numeric|min:0|max:99999999.99|decimal:0,2',
            'importe_consumido' => 'nullable|numeric|min:0|decimal:0,2|lte:importe_total',
        ];
    }
}