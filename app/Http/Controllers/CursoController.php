<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Enums\Turno;
class CursoController extends Controller
{
    // Mostrar formulario de creación
    public function listarCurso()
    {
        return response()->json(Curso::all(),200);
    }

    // Guardar un nuevo curso en la base de datos
    public function agregarCurso(Request $request)
    {
        $datos = $request->validate([
            'formacion_id'           => 'required|integer|max:11|exists:formacion,id',
            'codigo_curso'            => 'required|integer|max:11|unique:curso,id',
            'empresa_formadora'       => 'nullable|string|max:50',
            'fecha_inicio'            => 'required|date',
            'fecha_fin'               => 'required|date',
            'hora_inicio'             => 'nullable|date_format:H:i',
            'hora_final'              => 'nullable|date_format:H:i',
            'turno'                   => ['required', Rule::enum(Turno::class)],
            'aula'                    => 'nullable|string|max:45',
            'aula_virtual'            => 'nullable|string|max:45',
            'tecnico_comunidad'       => 'nullable|string|max:45',
            'observaciones'           => 'nullable|string|max:200',
            'peticiones_logistica_id' => 'nullable|exists:peticiones_logistica,id',
            'centro_id'               => 'nullable|exists:centro,id',
            'presupuesto_id'          => 'nullable|exists:presupuesto,id',
        ]);

        $curso = Curso::create($datos);

        return response()->json($curso, 201);
    }

    // Mostrar detalles de un curso
    public function obtenerCursoPorID($id)
    {
       $curso = Curso::find($id);

        if (! $curso) {
            return response()->json(['mensaje' => 'Curso no encontrado'], 404);
        }

        return response()->json($curso,201);
    }

    // Mostrar el formulario de edición con los datos actuales del curso
    public function actualizarCurso($id, Request $request)
    {
        $curso = Curso::findOrFail($id);
        
        $datos = $request->validate([
            'formacion_id'           => 'required|integer|max:11|exists:formacion,id',
            'codigo_curso'            => 'required|integer|max:11|unique:curso,id',
            'empresa_formadora'       => 'nullable|string|max:50',
            'fecha_inicio'            => 'required|date',
            'fecha_fin'               => 'required|date',
            'hora_inicio'             => 'nullable|date_format:H:i',
            'hora_final'              => 'nullable|date_format:H:i',
            'turno'                   => ['required', Rule::enum(Turno::class)],
            'aula'                    => 'nullable|string|max:45',
            'aula_virtual'            => 'nullable|string|max:45',
            'tecnico_comunidad'       => 'nullable|string|max:45',
            'observaciones'           => 'nullable|string|max:200',
            'peticiones_logistica_id' => 'nullable|exists:peticiones_logistica,id',
            'centro_id'               => 'nullable|exists:centro,id',
            'presupuesto_id'          => 'nullable|exists:presupuesto,id',
        ]);


        $curso->update($datos);

        return response()->json($curso,200);
    }


    // Eliminar un curso
    public function eliminarCurso($id)
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();

        return response()->json('curso eliminado con exito',201);
    }
}