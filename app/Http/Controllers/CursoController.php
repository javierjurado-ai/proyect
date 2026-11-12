<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    // Mostrar formulario de creación
    public function listarCurso()
    {
        return response()->json(Curso::all(),201);
    }

    // Guardar un nuevo curso en la base de datos
    public function agregarCurso(Request $request)
    {
        $datos = $request->validate([
            'curso_idcurso' => 'required|exists:formacion,idcurso',
            'codigo_grupo' => 'required|string|max:45|unique:curso,codigo_grupo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'centro_id' => 'required|exists:centro,id',
            'turno' => 'required',
        ]);

        $curso = Curso::create($datos);

        return response()->json($curso, 201);
    }

    // Mostrar detalles de un curso
    public function optenerCursoPorID($id)
    {
       $curso = Curso::find($id);

        if (! $curso) {
            return response()->json(['mensaje' => 'Centro no encontrado'], 404);
        }

        return response()->json($curso,201);
    }

    // Mostrar el formulario de edición con los datos actuales del curso
    public function actualizarCurso($id, Request $request)
    {
        $curso = Curso::findOrFail($id);
        
        $datos = $request->validate([
            'curso_idcurso' => 'required|exists:formacion,idcurso',
            'codigo_grupo' => 'required|string|max:45|unique:curso,codigo_grupo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'centro_id' => 'required|exists:centro,id',
            'turno' => 'required',
        ]);

        $curso->update($datos);

        return response()->json($curso,201);
    }


    // Eliminar un curso
    public function eliminarCurso($id)
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();

        return response()->json('curso eliminado con exito',201);
    }
}