<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'curso_idcurso'           => 'required|exists:formacion,idcurso',
            'codigo_grupo'            => 'required|string|max:45|unique:curso,codigo_grupo',
            'empresa_formadora'       => 'nullable|string|max:50',
            'fecha_inicio'            => 'required|date',
            'fecha_fin'               => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio'             => 'nullable|date_format:H:i',
            'hora_final'              => 'nullable|date_format:H:i',
            'turno'                   => 'nullable|string',
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
    public function optenerCursoPorID($id)
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
           'curso_idcurso'           => 'required|exists:formacion,idcurso',
            // Ignoramos el idgrupo actual para que no falle el unique al editar el mismo registro
            'codigo_grupo'            => ['required', 'string', 'max:45', Rule::unique('curso', 'codigo_grupo')->ignore($id, 'idgrupo')],
            'empresa_formadora'       => 'nullable|string|max:50',
            'fecha_inicio'            => 'required|date',
            'fecha_fin'               => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio'             => 'nullable|date_format:H:i',
            'hora_final'              => 'nullable|date_format:H:i',
            'turno'                   => 'required|string',
            'aula'                    => 'nullable|string|max:45',
            'aula_virtual'            => 'nullable|string|max:45',
            'tecnico_comunidad'       => 'nullable|string|max:45',
            'observaciones'           => 'nullable|string|max:200',
            'peticiones_logistica_id' => 'nullable|exists:peticiones_logistica,id',
            'centro_id'               => 'required|exists:centro,id',
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