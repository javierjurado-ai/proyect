<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ComunicacionFundae;
use App\Models\Enums\Tipo;
use Illuminate\Validation\Rule;

class ComunicacionFundaeController extends Controller
{
    public function listarComunicacionesFundae()
    {
        return response()->json(ComunicacionFundae::all());
    }
    public function optenerComunicacionFundaePorID($id)
    {
        $comunicaciones = ComunicacionFundae::findOrFail($id);

        return response()->json($comunicaciones, 201);
    }
    public function agregarComunicacionFundae(Request $request)
    {
        $datos = $request->validate([
            'grupo_idgrupo' => ['required', 'integer', 'exists:curso,idgrupo'],
            'tipo' => ['required', Rule::enum(Tipo::class)],
            'fecha_limite' => ['required', 'date'],
            'fecha_envio' => ['required', 'date'],
            'referencia' => ['required', 'string', 'max:60']
        ]);

        return response()->json(ComunicacionFundae::create($datos), 201);
    }

    public function actualizarComunicacionFundae($id, Request $request)
    {
        $comunicacion = ComunicacionFundae::findOrFail($id);

        $datos = $request->validate([
            'grupo_idgrupo' => ['required', 'integer', 'exists:curso,idgrupo'],
            'tipo'          => ['required', Rule::enum(Tipo::class)],
            'fecha_limite'  => ['required', 'date'],
            'fecha_envio'   => ['required', 'date'],
            'referencia'    => ['required', 'string', 'max:60'],
        ]);

        $comunicacion->update($datos);

        return response()->json($comunicacion, 200);
    }

    public function eliminarComunicacionFundae($id)
    {
        $comunicaciones = ComunicacionFundae::findOrFail($id);

        $comunicaciones->delete();

        return response()->json(['mensaje' => 'comunicacion fundae eliminada'], 200);
    }
}
