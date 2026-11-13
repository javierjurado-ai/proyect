<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presupuesto;

class PresupuestoController extends Controller
{
    // listar los presupuestos

    public function listarPresupuesto()
    {
        return response()->json(Presupuesto::all());
    }

    // Obtener los presupuesto por id

    public function obtenerPresupuestoPorID($id)
    {
        $presupuesto = Presupuesto::findOrFail($id);

        return response()->json($presupuesto, 200);
    }

    // agregar presupuesto

    public function agregarPresupuesto(Request $request)
    {
        $datos = $request->validate([
            'numero_alumnos'      => ['required', 'integer', 'min:1'],
            'nombre_formacion'    => ['required', 'string', 'max:45'],
            'horas_totales'       => ['required', 'integer', 'min:1'],
            'ubicacion_formacion' => ['required', 'string', 'max:90'],
            'objetivos'           => ['required', 'string', 'max:150'],
            'contenidos'          => ['required', 'string', 'max:450'],
            'importe_bonificado'  => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'fecha_bonificado'    => ['required', 'date'],
        ]);

        return response()->json(Presupuesto::create($datos), 201);
    }

    // actualizar un presupuesto

    public function actualizarPresupuesto($id, Request $request)
    {
        $presupuesto = Presupuesto::findOrFail($id);

        $datos = $request->validate([
            'numero_alumnos'      => ['required', 'integer', 'min:1'],
            'nombre_formacion'    => ['required', 'string', 'max:45'],
            'horas_totales'       => ['required', 'integer', 'min:1'],
            'ubicacion_formacion' => ['required', 'string', 'max:90'],
            'objetivos'           => ['required', 'string', 'max:150'],
            'contenidos'          => ['required', 'string', 'max:450'],
            'importe_bonificado'  => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'fecha_bonificado'    => ['required', 'date'],
        ]);

        $presupuesto->update($datos);

        return response()->json($presupuesto, 200);
    }

    // eliminar un presupuesto

    public function eliminarPresupuesto($id)
    {
        $presupuesto = Presupuesto::findOrFail($id);

        $presupuesto->delete();

        return response()->json(['mensaje' => 'presupuesto eliminado'], 200);
    }
}