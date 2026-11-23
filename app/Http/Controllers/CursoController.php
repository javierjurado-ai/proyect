<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Centro;
use App\Models\Curso;
use App\Models\CursoAlumno;
use App\Models\CursoDocente;
use App\Models\Enums\EstadoAula;
use App\Models\Enums\Modalidad;
use App\Models\Enums\TipoRol;
use App\Models\Enums\Turno;
use App\Models\Enums\UsoAula;
use App\Models\Formacion;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CursoController extends Controller
{
    ///////////////////Tablas//////////////////////
    public function crearTabla(Request $request)
    {
        $personaId = Auth::user()->persona_id;

        $rol     = Rol::where('persona_id', $personaId)->value('rol')?->value;
        $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

        if (! $rol || ! $persona) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['usuario' => 'No tienes acceso a la página.']);
        }

        $nombre = collect([$persona->nombre, $persona->primer_apellido, $persona->segundo_apellido])
            ->filter()
            ->implode(' ');

        $cursos = Curso::with('formacion')->get();

        return view('curso.index', compact('cursos', 'rol', 'nombre'));
    }

    public function visualizarCurso(Request $request, Curso $curso)
    {
        $curso->load(['formacion', 'presupuesto', 'peticionLogistica', 'centro']);

        $usuario = $this->datosUsuario();

        // Los alumnos solo los ven el administrador y el coordinador.
        // Para el resto ni siquiera se consultan, así no llegan a la vista.
        $puedeVerAlumnos = in_array($usuario['rol'], ['ADMINISTRADOR', 'COORDINADOR'], true);

        $alumnos = $puedeVerAlumnos
            ? $curso->alumnos()->with(['persona', 'empresa'])->get()
                ->sortBy(fn ($a) => $a->persona?->primer_apellido . ' ' . $a->persona?->nombre)
                ->values()
            : collect();

        return view('curso.visualizar', array_merge(
            compact('curso', 'alumnos', 'puedeVerAlumnos'),
            $usuario
        ));
    }

    public function crearTablaPorDocente($id)
    {
        $curso = Curso::with('formacion')->findOrFail($id);
        $idPersonas = CursoDocente::where('curso_id', $id)->pluck('persona_id');
        $docentes = Persona::whereIn('id', $idPersonas)->orderBy('primer_apellido')->get();

        return view('docente.tabla', compact('docentes', 'curso'));
    }

    public function crearTablaPorAlumno($id)
    {
        $curso = Curso::with('formacion')->findOrFail($id);
        $idPersonas = CursoAlumno::where('curso_id', $id)->pluck('persona_id');
        $alumnos = Persona::whereIn('id', $idPersonas)->orderBy('primer_apellido')->get();

        return view('alumno.tabla', compact('alumnos', 'curso'));
    }

    ////////////////////Roles////////////////////////////
    // OJO: el enum `rol` de la BD solo admite ADMINISTRADOR, COMERCIAL y COORDINADOR.
    // Estos dos métodos fallarán hasta que se amplíe el enum en la BD.
    public function agregarRolDocente($id)
    {
        return Rol::firstOrCreate([
            'rol'        => TipoRol::Docente,
            'persona_id' => $id,
        ]);
    }

    public function agregarRolAlumno($id)
    {
        return Rol::firstOrCreate([
            'rol'        => TipoRol::Alumno, // antes guardaba Docente por error
            'persona_id' => $id,
        ]);
    }

    /////////////////////Alumnos y docentes del curso///////////////////////////
    // Antes tenían curso_id = 3 fijo y solo enviaban curso_id/persona_id;
    // la BD exige el resto de columnas (NOT NULL).
    public function agregarCursoDocente(Request $request, $id)
    {
        $datos = $request->validate([
            'curso_id'           => 'required|integer|exists:curso,id',
            'empresa_id'         => 'required|integer|exists:empresa,id',
            'numero_horas'       => 'required|numeric|min:0|max:9999.99',
            'catagoria'          => ['required', Rule::in(['CUENTA AJENA', 'AUTONOMO'])],
            'tipo_contrato'      => ['required', Rule::in(['INDEFINIDO', 'FIJO DISCONTINUO', 'OBRA Y SERVICIO', 'TIEMPO COMPLETO'])],
            'convenio'           => 'nullable|string|max:45',
            'categoria'          => 'nullable|string|max:45',
            'IBAN'               => 'required|string|max:45',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date|after_or_equal:fecha_inicio',
            'horas_presenciales' => 'nullable|numeric|min:0',
            'horas_virtuales'    => 'nullable|numeric|min:0',
        ]);

        // Si vienen vacíos, que la BD ponga sus valores por defecto
        $datos = array_filter($datos, fn ($v) => $v !== null);
        $datos['persona_id'] = Persona::findOrFail($id)->id;

        return response()->json(CursoDocente::create($datos), 201);
    }

    public function agregarCursoAlumno(Request $request, $id)
    {
        $siNo = Rule::in(['SI', 'NO']);

        $datos = $request->validate([
            'curso_id'                              => 'required|integer|exists:curso,id',
            'empresa_id'                            => 'required|integer|exists:empresa,id',
            'jornada_laboral'                       => ['required', $siNo],
            'certificado_afectado_terrorismo'       => ['required', $siNo],
            'certificado_afectado_violencia_genero' => ['required', $siNo],
            'certificado_discapacidad'              => ['required', $siNo],
            'nivel_estudio'                         => ['required', Rule::in([
                'MENOS_PRIMARIA', 'PRIMARIA', 'ESO_EGB', 'BACHILLERATO_FP_MEDIO', 'CP_NIVEL_3',
                'FP_SUPERIOR', 'GRADO_DIPLOMATURA', 'LICENCIATURA_MASTER', 'DOCTORADO', 'OTRAS',
            ])],
            'categoria_profesional'                 => ['required', Rule::in([
                'DIRECTIVO', 'MANDO_INTERMEDIO', 'TECNICO', 'TRABAJADOR_CUALIFICADO', 'TRABAJADOR_BAJA_CUALIFICACION',
            ])],
            'erte'                                  => ['required', $siNo],
            'fijo_discontinuo'                      => ['required', $siNo],
            'diploma'                               => ['required', $siNo],
        ]);

        $datos['persona_id'] = Persona::findOrFail($id)->id;

        return response()->json(CursoAlumno::create($datos), 201);
    }

    ///////////////////Formulario////////////////////////
    public function crearFormulario()
    {
        return view('curso.formulario', array_merge(
            $this->datosDesplegables(),
            $this->datosUsuario()
        ));
    }

    // Muestra el formulario de edición con los datos del curso web
    public function editarCursoFormulario($id)
    {
        $curso = Curso::findOrFail($id);

        return view('curso.formulario', array_merge(
            compact('curso'),
            $this->datosDesplegables(),
            $this->datosUsuario()
        ));
    }

    ///////////////////Aulas////////////////////////
    // Muestra el formulario de alta de aulas
    public function crearFormularioAula()
    {
        // [id => "acción/grupo - formación"] para el desplegable de cursos
        $cursos = Curso::with('formacion')->orderBy('codigo_accion')->orderBy('codigo_grupo')->get()
            ->mapWithKeys(fn ($c) => [
                $c->id => $this->codigoCurso($c) . ($c->formacion ? ' - ' . $c->formacion->nombre : ''),
            ]);

        $estados = collect(EstadoAula::cases())
            ->mapWithKeys(fn ($e) => [$e->value => ucfirst(strtolower($e->value))]);

        $usos = collect(UsoAula::cases())
            ->mapWithKeys(fn ($u) => [$u->value => ucfirst(strtolower($u->value))]);

        return view('aula.formulario', compact('cursos', 'estados', 'usos'));
    }

    // Guarda el aula enviada desde el formulario
    public function guardarAula(Request $request)
    {
        $datos = $request->validate([
            'curso_id'      => 'nullable|integer|exists:curso,id', // en la BD puede ser NULL
            'nombre_aula'   => 'required|string|max:45',
            'capacidad'     => 'required|integer|min:1',
            'estado'        => ['required', Rule::enum(EstadoAula::class)],
            'uso_aula'      => ['required', Rule::enum(UsoAula::class)],
            'dimension'     => 'required|integer|min:0', // NOT NULL en la BD
            'observaciones' => 'nullable|string|max:450',
        ], [
            'required'        => 'Este campo es obligatorio.',
            'max'             => 'Máximo :max caracteres.',
            'integer'         => 'Debe ser un número entero.',
            'enum'            => 'El valor elegido no es válido.',
            'curso_id.exists' => 'El curso elegido no existe.',
            'capacidad.min'   => 'La capacidad debe ser al menos :min.',
            'dimension.min'   => 'La dimensión no puede ser negativa.',
        ]);

        Aula::create($datos);

        return redirect()
            ->route('aula.create')
            ->with('ok', 'Aula guardada correctamente.');
    }

    ///////////////////API////////////////////////
    public function listarCurso()
    {
        return response()->json(Curso::all(), 200);
    }

    public function obtenerCursoPorID($id)
    {
        $curso = Curso::find($id);

        if (! $curso) {
            return response()->json(['mensaje' => 'Curso no encontrado'], 404);
        }

        return response()->json($curso, 200);
    }

    // Guardar un nuevo curso (formulario web)
    public function agregarCurso(Request $request)
    {
        $datos = $request->validate($this->reglas($request), $this->mensajes());

        Curso::create($datos);

        return redirect()
            ->route('curso.crearTablaCurso')
            ->with('ok', 'Curso guardado correctamente.');
    }

    // Actualizar un curso (API)
    public function actualizarCurso($id, Request $request)
    {
        $curso = Curso::findOrFail($id);

        $datos = $request->validate($this->reglas($request, $curso->id), $this->mensajes());

        $curso->update($datos);

        return response()->json($curso, 200);
    }

    // Actualiza el curso enviado desde el formulario web
    public function actualizarCursoWeb(Request $request, $id)
    {
        $curso = Curso::findOrFail($id);

        $datos = $request->validate($this->reglas($request, $curso->id), $this->mensajes());

        $curso->update($datos);

        return redirect()
            ->route('curso.crearTablaCurso')
            ->with('ok', 'Curso actualizado correctamente.');
    }

    // Eliminar un curso (API)
    public function eliminarCurso($id)
    {
        $curso = Curso::findOrFail($id);

        try {
            $curso->delete();
            return response()->json(['mensaje' => 'Curso eliminado'], 200);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: el curso tiene alumnos, docentes u otros datos asociados',
            ], 409);
        }
    }

    // Elimina el curso desde la tabla web
    public function eliminarCursoWeb($id)
    {
        $curso = Curso::findOrFail($id);

        try {
            $curso->delete();
            return redirect()
                ->route('curso.crearTablaCurso')
                ->with('ok', 'Curso eliminado correctamente.');
        } catch (QueryException $e) {
            return redirect()
                ->route('curso.crearTablaCurso')
                ->with('error', 'No se puede eliminar: el curso tiene alumnos, docentes u otros datos asociados.');
        }
    }

    ///////////////////Auxiliares////////////////////////

    // Reglas comunes a crear y actualizar, según las columnas de la tabla curso
    private function reglas(Request $request, ?int $ignorarId = null): array
    {
        // El código de grupo no se repite dentro de la misma acción formativa
        $grupoUnico = Rule::unique('curso', 'codigo_grupo')
            ->where('codigo_accion', $request->input('codigo_accion'));

        if ($ignorarId) {
            $grupoUnico->ignore($ignorarId);
        }

        // Al editar, la hora llega de la BD como HH:MM:SS
        $hora = 'required|date_format:H:i,H:i:s';

        return [
            'formacion_id'            => 'nullable|integer|exists:formacion,id',
            'codigo_accion'           => 'required|string|max:45',
            'codigo_grupo'            => ['required', 'string', 'max:45', $grupoUnico],
            'empresa_formadora'       => 'required|string|max:50',
            'fecha_inicio'            => 'required|date',
            'fecha_fin'               => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio'             => $hora,
            'hora_final'              => $hora . '|after:hora_inicio',
            'turno'                   => ['required', Rule::enum(Turno::class)],
            'aula'                    => 'required|string|max:45',
            'tecnico_comunidad'       => 'required|string|max:45',
            'observaciones'           => 'nullable|string|max:200',
            'objetivos'               => 'required|string|max:500',
            'contenido'               => 'required|string|max:1000',
            'modalidad'               => ['required', Rule::enum(Modalidad::class)],
            'horas_presenciales'      => 'nullable|numeric|min:0|max:9999.99|required_if:modalidad,PRESENCIAL,MIXTA',
            'horas_teleformacion'     => 'nullable|numeric|min:0|max:9999.99|required_if:modalidad,TELEFORMACION,MIXTA',
            'nivel_formacion'         => 'nullable|integer|min:0|max:3',  // niveles 0 a 3
            'peticiones_logistica_id' => 'nullable|integer|exists:peticiones_logistica,id',
            'centro_id'               => 'nullable|integer|exists:centro,id',
            'presupuesto_id'          => 'nullable|integer|exists:presupuesto,id',
        ];
    }

    private function mensajes(): array
    {
        return [
            'required'                     => 'Este campo es obligatorio.',
            'max'                          => 'Máximo :max caracteres.',
            'date_format'                  => 'La hora debe tener el formato HH:MM.',
            'codigo_grupo.unique'          => 'Ya existe ese grupo para esta acción formativa.',
            'fecha_fin.after_or_equal'     => 'La fecha de fin no puede ser anterior a la de inicio.',
            'hora_final.after'             => 'La hora final debe ser posterior a la de inicio.',
            'horas_presenciales.required_if'  => 'Indica las horas presenciales para esta modalidad.',
            'horas_teleformacion.required_if' => 'Indica las horas de teleformación para esta modalidad.',
        ];
    }

    // Datos de los desplegables del formulario de curso
    private function datosDesplegables(): array
    {
        $formaciones = Formacion::where('activo', true)->orderBy('nombre')->pluck('nombre', 'id');
        $centros     = Centro::orderBy('nombre')->pluck('nombre', 'id');

        $turnos = collect(Turno::cases())->mapWithKeys(
            fn ($turno) => [$turno->value => ucfirst(strtolower($turno->value))]
        );

        // La modalidad ahora va en el curso (antes estaba en formación)
        $modalidades = collect(Modalidad::cases())->mapWithKeys(
            fn ($m) => [$m->value => ucfirst(strtolower($m->value))]
        );

        return compact('formaciones', 'centros', 'turnos', 'modalidades');
    }

    // Texto identificativo del curso: "acción/grupo"
    private function codigoCurso(Curso $curso): string
    {
        return $curso->codigo_accion . '/' . $curso->codigo_grupo;
    }

    // Rol y nombre del usuario conectado (los necesita el menú y la barra de arriba)
    private function datosUsuario(): array
    {
        $personaId = Auth::user()->persona_id;

        $rol     = Rol::where('persona_id', $personaId)->value('rol')?->value;
        $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

        $nombre = collect([$persona?->nombre, $persona?->primer_apellido, $persona?->segundo_apellido])
            ->filter()
            ->implode(' ');

        return compact('rol', 'nombre');
    }
}