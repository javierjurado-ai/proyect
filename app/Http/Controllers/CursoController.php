<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Enums\Turno;
use App\Models\Centro;
use App\Models\CursoAlumno;
use App\Models\CursoDocente;
use App\Models\Enums\TipoRol;
use App\Models\Formacion;
use App\Models\Persona;
use App\Models\Rol;

class CursoController extends Controller
{
    ///////////////////Tablas//////////////////////
    public function crearTabla()
    {
        $cursos = Curso::all();

        return view('curso.tabla', compact('cursos'));
    }
    public function crearTablaPorDocente($id)
    {
        $idPersonas = CursoDocente::where('curso_id', $id)->pluck('persona_id');
        $docentes = Persona::findMany($idPersonas);

        return view('docente.tabla', compact('docentes'));
    }
    public function crearTablaPorAlumno($id)
    {
        $idPersonas = CursoAlumno::where('curso_id', $id)->pluck('persona_id');
        $alumnos = Persona::findMany($idPersonas);

        return view('alumno.tabla', compact('alumnos'));
    }
    ////////////////////Roles////////////////////////////
    public function agregarRolDocente($id)
    {
        return Rol::create([
            'rol'        => TipoRol::Docente,
            'persona_id' => $id,
        ]);
    }
    public function agregarRolAlumno($id)
    {
        return Rol::create([
            'rol'        => TipoRol::Docente,
            'persona_id' => $id,
        ]);
    }
    /////////////////////AgregarManyToMany///////////////////////////
    public function agregarCursoDocente($id)
    {
        return CursoDocente::create([
            'curso_id'        => 3,
            'persona_id' => $id,
        ]);
    }
    public function agregarCursoAlumno($id)
    {
        return CursoDocente::create([
            'curso_id'        => 3,
            'persona_id' => $id,
        ]);
    }
    ///////////////////Formulario////////////////////////
    public function crearFormulario()
    {
        $formaciones = Formacion::orderBy('nombre')->pluck('nombre', 'id');
        $centros = Centro::orderBy('nombre')->pluck('nombre', 'id');
        $turnos = collect(Turno::cases())->mapWithKeys(
            fn($turno) => [$turno->value => ucfirst($turno->name)]
        );


        return view('curso.formulario', compact('centros', 'formaciones', 'turnos'));
    }


    // Mostrar formulario de creación
    public function listarCurso()
    {
        return response()->json(Curso::all(), 200);
    }

    // Guardar un nuevo curso en la base de datos
    public function agregarCurso(Request $request)
    {
        $datos = $request->validate([
            'formacion_id'            => 'required|integer|exists:formacion,id',
            'codigo_curso'            => 'required|integer|min:1|unique:curso,codigo_curso',
            'empresa_formadora'       => 'nullable|string|max:50',
            'fecha_inicio'            => 'required|date',
            'fecha_fin'               => 'required|date|after_or_equal:fecha_inicio',
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

        if ($request->expectsJson()) {
            return response()->json($curso, 201);
        }

        return redirect()->route('crearFormulario')->with('status', 'Curso guardado');

        return back()->with('status', 'Curso guardado');
    }

    // Mostrar detalles de un curso
    public function obtenerCursoPorID($id)
    {
        $curso = Curso::find($id);

        if (! $curso) {
            return response()->json(['mensaje' => 'Curso no encontrado'], 404);
        }

        return response()->json($curso, 201);
    }

    // Mostrar el formulario de edición con los datos actuales del curso
    public function actualizarCurso($id, Request $request)
    {
        $curso = Curso::findOrFail($id);

        $datos = $request->validate([
            'formacion_id'            => 'required|integer|exists:formacion,id',
            'codigo_curso'            => 'required|integer|min:1|unique:curso,codigo_curso',
            'empresa_formadora'       => 'nullable|string|max:50',
            'fecha_inicio'            => 'required|date',
            'fecha_fin'               => 'required|date|after_or_equal:fecha_inicio',
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

        return response()->json($curso, 200);
    }


    // Eliminar un curso
    public function eliminarCurso($id)
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();

        return response()->json('curso eliminado con exito', 201);
    }
}
