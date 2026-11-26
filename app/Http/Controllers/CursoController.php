<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Centro;
use App\Models\Curso;
use App\Models\CursoAlumno;
use App\Models\CursoCalendario;
use App\Models\CursoContacto;
use App\Models\CursoDocente;
use App\Models\Empresa;
use App\Models\Enums\Categoria;
use App\Models\Enums\CategoriaProfesional;
use App\Models\Enums\EstadoAula;
use App\Models\Enums\EstadoLogistica;
use App\Models\enums\LOPD;
use App\Models\Enums\Modalidad;
use App\Models\Enums\NivelEstudio;
use App\Models\Enums\TipoContrato;
use App\Models\Enums\TipoRol;
use App\Models\Enums\Turno;
use App\Models\Enums\UsoAula;
use App\Models\Formacion;
use App\Models\Persona;
use App\Models\PeticionesLogistica;
use App\Models\PeticionProducto;
use App\Models\Rol;
use App\Services\CalendarioCursoService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CursoController extends Controller
{
    public function crearTabla(Request $request)
    {
        $personaId = Auth::user()->persona_id;
        $rol       = Rol::where('persona_id', $personaId)->value('rol')?->value;
        $persona   = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

        if (! $rol || ! $persona) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['usuario' => 'No tienes acceso a la página.']);
        }

        $nombre = collect([$persona->nombre, $persona->primer_apellido, $persona->segundo_apellido])->filter()->implode(' ');

        // Capturamos cada filtro individualmente
        $fInicio         = trim((string) $request->query('fecha_inicio', ''));
        $fFin            = trim((string) $request->query('fecha_fin', ''));
        $formacionNombre = trim((string) $request->query('formacion_nombre', ''));
        $empresaCliente  = trim((string) $request->query('empresa_cliente', ''));
        $modalidad       = $request->query('modalidad', '');
        $codigoAccion    = trim((string) $request->query('codigo_accion', ''));
        $codigoGrupo     = trim((string) $request->query('codigo_grupo', ''));

        // Consulta principal de cursos con filtros independientes
        $cursos = Curso::query()
            ->with(['formacion', 'centro.empresa'])
            ->when($fInicio !== '', fn($q) => $q->where('fecha_inicio', 'like', "%{$fInicio}%"))
            ->when($fFin !== '', fn($q) => $q->where('fecha_fin', 'like', "%{$fFin}%"))
            ->when($formacionNombre !== '', fn($q) => $q->whereHas('formacion', fn($sub) => $sub->where('nombre', 'like', "%{$formacionNombre}%")))
            ->when($empresaCliente !== '', fn($q) => $q->whereHas('centro.empresa', fn($sub) => $sub->where('nombre', 'like', "%{$empresaCliente}%")))
            ->when($modalidad !== '', fn($q) => $q->where('modalidad', $modalidad))
            ->when($codigoAccion !== '', fn($q) => $q->where('codigo_accion', 'like', "%{$codigoAccion}%"))
            ->when($codigoGrupo !== '', fn($q) => $q->where('codigo_grupo', 'like', "%{$codigoGrupo}%"))
            ->orderByDesc('fecha_inicio')
            ->paginate(15)
            ->withQueryString();

        // Opciones para los datalists y selects
        $formacionesFiltro = Formacion::whereHas('cursos')->orderBy('nombre')->pluck('nombre');
        $empresasFiltro    = Empresa::whereHas('centros.cursos')->orderBy('nombre')->pluck('nombre');
        $modalidadesFiltro = collect(Modalidad::cases())->mapWithKeys(fn($m) => [$m->value => ucfirst(strtolower($m->value))]);
        $accionesFiltro    = Curso::whereNotNull('codigo_accion')->distinct()->pluck('codigo_accion', 'codigo_accion');
        $gruposFiltro      = Curso::whereNotNull('codigo_grupo')->distinct()->pluck('codigo_grupo', 'codigo_grupo');

        return view('curso.index', compact(
            'cursos', 'rol', 'nombre',
            'fInicio', 'fFin', 'formacionNombre', 'empresaCliente', 'modalidad', 'codigoAccion', 'codigoGrupo',
            'formacionesFiltro', 'empresasFiltro', 'modalidadesFiltro', 'accionesFiltro', 'gruposFiltro'
        ));
    }

    public function visualizarCurso(Request $request, Curso $curso)
    {
        $personaId = Auth::user()->persona_id;
        $curso->load(['formacion', 'presupuesto', 'peticionLogistica', 'centro']);
        $rol     = Rol::where('persona_id', $personaId)->value('rol')?->value;
        $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);
        $nombre = collect([$persona->nombre, $persona->primer_apellido, $persona->segundo_apellido])
            ->filter()
            ->implode(' ');
        $usuario = $this->datosUsuario();

        // Los alumnos solo los ven el administrador y el coordinador.
        // Para el resto ni siquiera se consultan, así no llegan a la vista.
        $puedeVerAlumnos = in_array($usuario['rol'], ['ADMINISTRADOR', 'COORDINADOR'], true);

        // Cargamos las relaciones necesarias incluyendo los alumnos y sus datos personales
        $curso->load([
            'formacion',
            'presupuesto',
            'centro',
            'alumnos.persona',
            'alumnos.empresa',
            'docentes.persona',
        ]);

        $peticionSinEstado = PeticionesLogistica::with(['solicitante', 'centro'])
            ->where('curso_id', $curso->id)
            ->whereNull('estado')
            ->first();

        $peticiones = PeticionesLogistica::with(['solicitante', 'centro', 'peticionProductos'])
            ->where('curso_id', $curso->id)
            ->whereNotNull('estado')
            ->latest('fecha')
            ->get();

        $productos = $peticionSinEstado
            ? PeticionProducto::where('peticiones_logistica_id', $peticionSinEstado->id)->get()
            : collect();

        return view('curso.visualizar', compact('curso', 'rol', 'nombre', 'persona', 'nombre', 'productos', 'peticiones'));
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

    public function crearTablaAula(Request $request)
    {
        $datosUsuario = $this->datosUsuario();
        $rol = $datosUsuario['rol'];
        $nombre = $datosUsuario['nombre'];

        $aulas = Aula::with('curso.formacion')->get();

        return view('aula.tabla', compact('aulas', 'rol', 'nombre'));
    }

    public function agregarAlumnos(Curso $curso)
    {
        $personasDisponibles = Persona::whereNotIn(
            'id',
            $curso->alumnos()->select('persona_id')
        )->get();
        $empresas = Empresa::orderBy('nombre')->pluck('nombre', 'id');
        $lopd = collect(LOPD::cases())
            ->mapWithKeys(fn($l) => [$l->value => ucfirst(strtolower($l->value))]);
        $categoriasProfesional = collect(CategoriaProfesional::cases())
            ->mapWithKeys(fn($c) => [$c->value => ucfirst(strtolower($c->value))]);
        $nivelesEstudio = collect(NivelEstudio::cases())
            ->mapWithKeys(fn($c) => [$c->value => ucfirst(strtolower($c->value))]);
        return view('curso.tablaAlumno', compact('curso', 'personasDisponibles', 'empresas', 'lopd', 'categoriasProfesional', 'nivelesEstudio'));
    }
    public function agregarDocentes(Curso $curso)
    {
        $personasDisponibles = Persona::whereNotIn(
            'id',
            $curso->alumnos()->select('persona_id')
        )->get();
        $empresas = Empresa::orderBy('nombre')->pluck('nombre', 'id');
        $lopd = collect(LOPD::cases())
            ->mapWithKeys(fn($l) => [$l->value => ucfirst(strtolower($l->value))]);
        $categorias = collect(Categoria::cases())
            ->mapWithKeys(fn($c) => [$c->value => ucfirst(strtolower($c->value))]);
        $contratos = collect(TipoContrato::cases())
            ->mapWithKeys(fn($c) => [$c->value => ucfirst(strtolower($c->value))]);
        return view('curso.tablaDocente', compact('curso', 'personasDisponibles', 'empresas', 'lopd', 'categorias', 'contratos'));
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
        $datos = array_filter($datos, fn($v) => $v !== null);
        $datos['persona_id'] = Persona::findOrFail($id)->id;

        return response()->json(CursoDocente::create($datos), 201);
    }

    public function guardarAlumnos(Request $request, Curso $curso)
    {
        //para la función para visualizar la petición
        //dd($request->input('alumnos'), (new \App\Models\Empresa)->getTable(), (new \App\Models\Empresa)->getKeyName());
        $request->validate([
            'alumnos'                                         => ['required', 'array', 'min:1'],
            'alumnos.*.persona_id'                            => ['required', 'integer', 'distinct', Rule::exists((new Persona)->getTable(), 'id')],
            'alumnos.*.empresa_id' => ['required', Rule::exists((new Empresa)->getTable(), (new Empresa)->getKeyName())],
            'alumnos.*.jornada_laboral'                       => ['required', 'string'],
            'alumnos.*.categoria_profesional'                 => ['required', 'string'],
            'alumnos.*.erte'                                  => ['required', 'string'],
            'alumnos.*.fijo_discontinuo'                      => ['required', 'string'],
            'alumnos.*.nivel_estudio'                         => ['required', 'string'],
            'alumnos.*.diploma'                               => ['required', 'string'],
            'alumnos.*.certificado_discapacidad'              => ['required', 'string'],
            'alumnos.*.certificado_afectado_terrorismo'       => ['required', 'string'],
            'alumnos.*.certificado_afectado_violencia_genero' => ['required', 'string'],
        ], [
            'alumnos.required'   => 'Añade al menos un alumno.',
            'alumnos.*.required' => 'Faltan datos de algún alumno.',
        ]);

        // Personas que ya están matriculadas en este curso (evita duplicados).
        $yaMatriculadas = CursoAlumno::where('curso_id', $curso->id)
            ->pluck('persona_id')
            ->all();

        $guardados = 0;

        DB::transaction(function () use ($request, $curso, $yaMatriculadas, &$guardados) {
            foreach ($request->input('alumnos') as $datos) {
                if (in_array((int) $datos['persona_id'], $yaMatriculadas, true)) {
                    continue;
                }

                CursoAlumno::create([
                    'curso_id'   => $curso->id, // viene de la ruta, no del formulario
                    'persona_id' => $datos['persona_id'],
                    'empresa_id' => $datos['empresa_id'],
                    'jornada_laboral'                       => $datos['jornada_laboral'],
                    'categoria_profesional'                 => $datos['categoria_profesional'],
                    'erte'                                  => $datos['erte'],
                    'fijo_discontinuo'                      => $datos['fijo_discontinuo'],
                    'nivel_estudio'                         => $datos['nivel_estudio'],
                    'diploma'                               => $datos['diploma'],
                    'certificado_discapacidad'              => $datos['certificado_discapacidad'],
                    'certificado_afectado_terrorismo'       => $datos['certificado_afectado_terrorismo'],
                    'certificado_afectado_violencia_genero' => $datos['certificado_afectado_violencia_genero'],
                ]);

                $guardados++;
            }
        });

        return redirect()
            ->route('curso.visualizar', $curso)
            ->with('status', $guardados === 1
                ? 'Alumno agregado correctamente.'
                : "{$guardados} alumnos agregados correctamente.");
    }
    public function guardarDocentes(Request $request, Curso $curso)
    {
        // Para ver lo que llega:
        // dd($request->input('docentes'));

        $request->validate([
            'docentes'                      => ['required', 'array', 'min:1'],
            'docentes.*.persona_id'         => ['required', 'integer', 'distinct', Rule::exists((new Persona)->getTable(), (new Persona)->getKeyName())],
            'docentes.*.empresa_id'         => ['required', Rule::exists((new Empresa)->getTable(), (new Empresa)->getKeyName())],
            'docentes.*.categoria'          => ['required', 'string', 'max:45'],
            'docentes.*.tipo_contrato'      => ['required', 'string'],
            'docentes.*.convenio'           => ['required', 'string', 'max:45'],
            'docentes.*.IBAN'               => ['required', 'string', 'max:45'],
            'docentes.*.numero_horas'       => ['required', 'numeric', 'min:0'],
            'docentes.*.horas_presenciales' => ['nullable', 'numeric', 'min:0'],
            'docentes.*.horas_virtuales'    => ['nullable', 'numeric', 'min:0'],
            'docentes.*.fecha_inicio'       => ['required', 'date'],
            'docentes.*.fecha_fin'          => ['required', 'date', 'after_or_equal:docentes.*.fecha_inicio'],
        ], [
            'docentes.required'   => 'Añade al menos un docente.',
            'docentes.*.required' => 'Faltan datos de algún docente.',
        ]);

        // Personas que ya son docentes de este curso (evita duplicados).
        $yaAsignados = CursoDocente::where('curso_id', $curso->id)
            ->pluck('persona_id')
            ->all();

        $guardados = 0;

        DB::transaction(function () use ($request, $curso, $yaAsignados, &$guardados) {
            foreach ($request->input('docentes') as $datos) {
                if (in_array((int) $datos['persona_id'], $yaAsignados, true)) {
                    continue;
                }

                CursoDocente::create([
                    'curso_id'           => $curso->id, // viene de la ruta, no del formulario
                    'persona_id'         => $datos['persona_id'],
                    'empresa_id'         => $datos['empresa_id'],
                    'categoria'          => $datos['categoria'],
                    'tipo_contrato'      => $datos['tipo_contrato'],
                    'convenio'           => $datos['convenio'],
                    'IBAN'               => $datos['IBAN'],
                    'numero_horas'       => $datos['numero_horas'],
                    'horas_presenciales' => $datos['horas_presenciales'] ?? null,
                    'horas_virtuales'    => $datos['horas_virtuales'] ?? null,
                    'fecha_inicio'       => $datos['fecha_inicio'],
                    'fecha_fin'          => $datos['fecha_fin'],
                ]);

                $guardados++;
            }
        });

        return redirect()
            ->route('curso.visualizar', $curso)
            ->with('status', $guardados === 1
                ? 'Docente agregado correctamente.'
                : "{$guardados} docentes agregados correctamente.");
    }
    ///////////////////Formulario////////////////////////
    public function crearFormulario()
    {
        // Añadimos las aulas disponibles ordenadas por nombre
        $aulas = \App\Models\Aula::select('id', 'centro_id', 'nombre_aula')->orderBy('nombre_aula')->get();

        return view('curso.formulario', array_merge(
            $this->datosDesplegables(),
            $this->datosUsuario(),
            compact('aulas') // <--- Pasamos las aulas a la vista
        ));
    }

    // Muestra el formulario de edición con los datos del curso web
    public function editarCursoFormulario($id)
    {
        $curso = Curso::findOrFail($id);

        // Días que ya tiene en su calendario (para el apartado "Calendario" del formulario)
        $diasCalendario = $curso->calendario()->count();

        return view('curso.formulario', array_merge(
            compact('curso'),
            [
                'aulas' => Aula::all() // <-- Asegúrate de pasar esto aquí
            ],
            $this->datosDesplegables(),
            $this->datosUsuario()
        ));
    }

    ///////////////////Aulas////////////////////////

    // Muestra el formulario de alta de aulas asociado a un centro
    public function crearFormularioAula($centro_id)
    {
        $centro = Centro::findOrFail($centro_id);

        // [id => "acción/grupo - formación"] para el desplegable de cursos
        $cursos = Curso::with('formacion')->orderBy('codigo_accion')->orderBy('codigo_grupo')->get()
            ->mapWithKeys(fn($c) => [
                $c->id => $this->codigoCurso($c) . ($c->formacion ? ' - ' . $c->formacion->nombre : ''),
            ]);

        $estados = collect(EstadoAula::cases())
            ->mapWithKeys(fn($e) => [$e->value => ucfirst(strtolower($e->value))]);

        $usos = collect(UsoAula::cases())
            ->mapWithKeys(fn($u) => [$u->value => ucfirst(strtolower($u->value))]);

        // Pasamos el $centro a la vista junto con los datos de usuario
        return view('centro.formulario-aula', compact('centro', 'cursos', 'estados', 'usos') + $this->datosUsuario());
    }

    // Guarda el aula asociada a un centro
    public function guardarAula(Request $request, $centro_id)
    {
        $centro = Centro::findOrFail($centro_id);

        $datos = $request->validate([
            'curso_id'      => 'nullable|integer|exists:curso,id',
            'nombre_aula'   => 'required|string|max:45',
            'capacidad'     => 'required|integer|min:1',
            'estado'        => ['required', Rule::enum(EstadoAula::class)],
            'uso_aula'      => ['required', Rule::enum(UsoAula::class)],
            'dimension'     => 'required|integer|min:0',
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

        // Asignamos el centro_id antes de crear el aula
        $datos['centro_id'] = $centro->id;

        Aula::create($datos);

        // Redirigimos de vuelta a la pestaña del centro
        return redirect()
            ->to("/view/centro/{$centro->id}")
            ->with('ok', 'Aula guardada correctamente.');
    }

    // Muestra el formulario de edición de un aula
    public function editarAulaFormulario($id)
    {
        $aula = Aula::findOrFail($id);

        // Recuperamos opcionalmente el centro asociado si lo tiene
        $centro = $aula->centro_id ? Centro::find($aula->centro_id) : null;

        $cursos = Curso::with('formacion')->orderBy('codigo_accion')->orderBy('codigo_grupo')->get()
            ->mapWithKeys(fn($c) => [
                $c->id => $this->codigoCurso($c) . ($c->formacion ? ' - ' . $c->formacion->nombre : ''),
            ]);

        $estados = collect(EstadoAula::cases())
            ->mapWithKeys(fn($e) => [$e->value => ucfirst(strtolower($e->value))]);

        $usos = collect(UsoAula::cases())
            ->mapWithKeys(fn($u) => [$u->value => ucfirst(strtolower($u->value))]);

        return view('centro.formulario-aula', compact('aula', 'centro', 'cursos', 'estados', 'usos') + $this->datosUsuario());
    }

    ///////////////////Calendario del curso////////////////////////

    // Formulario para añadir un día (la fecha puede venir del clic en el calendario: ?fecha=AAAA-MM-DD)
    public function crearFormularioDia(Curso $curso)
    {
        return view('curso.formularioFecha', array_merge(
            compact('curso'),
            $this->datosUsuario()
        ));
    }

    // Guarda un día nuevo en el calendario del curso
    public function guardarDia(Request $request, Curso $curso)
    {
        $datos = $request->validate($this->reglasDia($curso), $this->mensajesDia());

        $curso->calendario()->create($datos);

        return redirect()
            ->route('curso.visualizar', [$curso, 'pestana' => 'calendario'])
            ->with('ok', 'Día añadido al calendario.');
    }

    // Formulario para modificar un día que ya existe
    public function editarFormularioDia(Curso $curso, CursoCalendario $dia)
    {
        abort_unless((int) $dia->curso_id === (int) $curso->id, 404);

        return view('curso.formularioFecha', array_merge(
            compact('curso', 'dia'),
            $this->datosUsuario()
        ));
    }

    // Guarda los cambios de un día
    public function actualizarDia(Request $request, Curso $curso, CursoCalendario $dia)
    {
        abort_unless((int) $dia->curso_id === (int) $curso->id, 404);

        $datos = $request->validate($this->reglasDia($curso, $dia->id), $this->mensajesDia());

        $dia->update($datos);

        return redirect()
            ->route('curso.visualizar', [$curso, 'pestana' => 'calendario'])
            ->with('ok', 'Día del ' . $dia->fecha->format('d/m/Y') . ' modificado.');
    }

    // Cancela (borra) la sesión de un día
    public function cancelarDia(Curso $curso, CursoCalendario $dia)
    {
        abort_unless((int) $dia->curso_id === (int) $curso->id, 404);

        $fecha = $dia->fecha->format('d/m/Y');
        $dia->delete();

        return redirect()
            ->route('curso.visualizar', [$curso, 'pestana' => 'calendario'])
            ->with('ok', 'Sesión del ' . $fecha . ' cancelada.');
    }

    // Crea los días del calendario a partir del horario general del curso
    // (fecha inicio → fecha fin, hora inicio → hora fin). No toca los días que ya existan.
    public function generarDias(Request $request, Curso $curso)
    {
        if (! $curso->fecha_inicio || ! $curso->fecha_fin || ! $curso->hora_inicio || ! $curso->hora_final) {
            return back()->withErrors(['generar' => 'El curso necesita fecha de inicio, fecha de fin y horario para generar los días.']);
        }

        $patron = match ($request->input('modo')) {
            'todos'   => ['modo' => 'semana', 'dias' => [1, 2, 3, 4, 5, 6, 7]],
            'alterno' => ['modo' => 'alterno'],
            default   => ['modo' => 'semana', 'dias' => [1, 2, 3, 4, 5]],
        };

        $total = app(CalendarioCursoService::class)->generar($curso, $patron);

        return redirect()
            ->route('curso.visualizar', [$curso, 'pestana' => 'calendario'])
            ->with('ok', $total === 0
                ? 'No había días nuevos que añadir.'
                : "Se han añadido {$total} " . ($total === 1 ? 'día' : 'días') . ' al calendario con el horario del curso.');
    }

    // Reglas de un día del calendario. Vale cualquier día de la semana (también fines de semana).
    private function reglasDia(Curso $curso, ?int $ignorarId = null): array
    {
        $fecha = [
            'required',
            'date',
            Rule::unique('curso_calendario', 'fecha')
                ->where('curso_id', $curso->id)
                ->ignore($ignorarId),
        ];
        if ($curso->fecha_inicio) {
            $fecha[] = 'after_or_equal:' . \Illuminate\Support\Carbon::parse($curso->fecha_inicio)->toDateString();
        }
        if ($curso->fecha_fin) {
            $fecha[] = 'before_or_equal:' . \Illuminate\Support\Carbon::parse($curso->fecha_fin)->toDateString();
        }

        return [
            'fecha'        => $fecha,
            'hora_inicio1' => ['required', 'date_format:H:i'],
            'hora_fin1'    => ['required', 'date_format:H:i', 'after:hora_inicio1'],
            'hora_inicio2' => ['nullable', 'required_with:hora_fin2', 'date_format:H:i', 'after_or_equal:hora_fin1'],
            'hora_fin2'    => ['nullable', 'required_with:hora_inicio2', 'date_format:H:i', 'after:hora_inicio2'],
        ];
    }

    private function mensajesDia(): array
    {
        return [
            'required'                => 'Este campo es obligatorio.',
            'required_with'           => 'Si rellenas el segundo tramo, indica inicio y fin.',
            'date'                    => 'La fecha no es válida.',
            'date_format'             => 'La hora no es válida (formato HH:MM).',
            'fecha.unique'            => 'Ese día ya está en el calendario del curso. Modifícalo en lugar de crearlo otra vez.',
            'fecha.after_or_equal'    => 'La fecha es anterior al inicio del curso.',
            'fecha.before_or_equal'   => 'La fecha es posterior al final del curso.',
            'hora_fin1.after'         => 'La hora de fin debe ser posterior a la de inicio.',
            'hora_inicio2.after_or_equal' => 'El segundo tramo debe empezar después de que acabe el primero.',
            'hora_fin2.after'         => 'La hora de fin debe ser posterior a la de inicio.',
        ];
    }

    public function crearFormularioLogistica(Curso $curso)
    {
        $cursos = Curso::with('formacion')->orderBy('codigo_accion')->orderBy('codigo_grupo')->get()
            ->mapWithKeys(fn($c) => [
                $c->id => $this->codigoCurso($c) . ($c->formacion ? ' - ' . $c->formacion->nombre : ''),
            ]);

        return view('logistica.formularioPeticionProducto', compact('cursos', 'curso') + $this->datosUsuario());
    }
    public function crearPeticionLogistica(Request $request, Curso $curso)
    {
        $datos = $request->validate([
            'producto'        => 'required|string|max:45',
            'cantidad'        => 'required|integer|min:1|max:99',
            'direccion_envio' => 'nullable|string|max:255',
            'observaciones'   => 'nullable|string|max:200',
        ]);

        $peticion = PeticionesLogistica::firstOrCreate(
            [
                'curso_id' => $curso->id,
                'estado'   => null,
            ],
            [
                'fecha'           => now(),
                'centro_id'       => $curso->centro_id,
                'solicitante_id'  => Auth::user()->persona_id ?? null,
                'direccion_envio' => $datos['direccion_envio'] ?? $curso->centro?->direccion,
            ]
        );

        PeticionProducto::create([
            'peticiones_logistica_id' => $peticion->id,
            'producto'                => $datos['producto'],
            'cantidad'                => $datos['cantidad'],
            'observaciones'           => $datos['observaciones'] ?? null,
        ]);

        return redirect()
            ->route('curso.visualizar', $curso)
            ->with('ok', 'Petición creada correctamente.');
    }

    public function enviarPeticionesLogistica(Request $request, Curso $curso)
    {
        $datos = $request->validate([
            'direccion_envio' => 'nullable|string|max:255',
            'fecha_necesaria' => 'nullable|date',
        ]);

        $peticion = PeticionesLogistica::where('curso_id', $curso->id)
            ->whereNull('estado')
            ->firstOrFail();

        $peticion->update([
            'fecha'           => now(),
            'solicitante_id'  => Auth::user()->persona_id,
            'direccion_envio' => $datos['direccion_envio'] ?? $peticion->direccion_envio,
            'fecha_necesaria' => $datos['fecha_necesaria'] ?? null,
            'estado'          => EstadoLogistica::Pendiente,
        ]);

        return redirect()
            ->route('curso.mostrar', $curso)
            ->with('ok', 'Productos enviados correctamente.');
    }
    // Actualiza el aula en la base de datos
    public function actualizarAula(Request $request, $id)
    {
        $aula = Aula::findOrFail($id);

        $datos = $request->validate([
            'curso_id'      => 'nullable|integer|exists:curso,id',
            'nombre_aula'   => 'required|string|max:45',
            'capacidad'     => 'required|integer|min:1',
            'estado'        => ['required', Rule::enum(EstadoAula::class)],
            'uso_aula'      => ['required', Rule::enum(UsoAula::class)],
            'dimension'     => 'required|integer|min:0',
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

        // Guardamos el centro_id antes de actualizar
        $centroId = $aula->centro_id;

        $aula->update($datos);

        // Si pertenece a un centro, lo devolvemos explícitamente a la vista de ese centro
        if ($centroId) {
            return redirect()->to("/view/centro/{$centroId}")->with('success', 'Aula actualizada correctamente.');
        }

        return redirect()->route('aula.index')->with('success', 'Aula actualizada correctamente.');
    }

    // Elimina el aula
    public function eliminarAula($id)
    {
        $aula = Aula::findOrFail($id);

        // Guardamos el centro_id ANTES de borrar el registro
        $centroId = $aula->centro_id;

        try {
            $aula->delete();

            if ($centroId) {
                return redirect()->to("/view/centro/{$centroId}")->with('ok', 'Aula eliminada correctamente.');
            }

            return redirect()
                ->route('aula.index')
                ->with('ok', 'Aula eliminada correctamente.');
        } catch (QueryException $e) {
            if ($centroId) {
                return redirect()->to("/view/centro/{$centroId}")->with('error', 'No se puede eliminar el aula porque tiene registros asociados.');
            }

            return redirect()
                ->route('aula.index')
                ->with('error', 'No se puede eliminar el aula porque tiene registros asociados.');
        }
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
        $cal   = $request->validate($this->reglasCalendario(true), $this->mensajesCalendario());

        // Si es teleformación y el aula viene vacía, le asignamos un valor por defecto para la BD
        if (($datos['modalidad'] ?? '') === 'TELEFORMACION' && empty($datos['aula'])) {
            $datos['aula'] = 'Online';
        }

        // La persona de contacto es la del usuario que ha iniciado sesión.
        $personaId = Auth::user()->persona_id;
        $anadidos = 0;

        DB::transaction(function () use ($datos, $cal, $personaId, &$anadidos) {
            // El curso se crea solo con sus propios campos.
            $curso = Curso::create(
                collect($datos)->except(['coordinador', 'actividad'])->all()
            );

            // Días del calendario según el patrón elegido (no se guarda en la tabla curso)
            $anadidos = $this->generarCalendario($curso, $cal);

            // Contacto con el id del curso recién creado y la persona del usuario.
            // 'coordinador' es obligatorio en la tabla (varchar 45): se guarda su nombre completo.
            $persona = Persona::find($personaId);
            $nombreCoordinador = collect([$persona?->nombre, $persona?->primer_apellido, $persona?->segundo_apellido])
                ->filter()
                ->implode(' ');

            CursoContacto::create([
                'curso_id'    => $curso->id,
                'persona_id'  => $personaId,
                'coordinador' => mb_substr($nombreCoordinador, 0, 45),
                'actividad'   => 1,
            ]);
        });

        return redirect()
            ->route('curso.crearTablaCurso')
            ->with('ok', 'Curso guardado correctamente.'
                . ($anadidos ? " Se han creado {$anadidos} días en su calendario." : ''));
    }

    // Actualizar un curso (API)
    public function actualizarCurso($id, Request $request)
    {
        $curso = Curso::findOrFail($id);

        $datos = $request->validate($this->reglas($request, $curso->id), $this->mensajes());

        DB::transaction(function () use ($curso, $datos) {
            $antes = $curso->only(CalendarioCursoService::CAMPOS);
            $curso->update($datos);
            app(CalendarioCursoService::class)->ajustar($curso, $antes);
        });

        return response()->json($curso->fresh(), 200);
    }

    // Actualiza el curso enviado desde el formulario web
    public function actualizarCursoWeb(Request $request, $id)
    {
        $curso = Curso::findOrFail($id);

        $datos = $request->validate($this->reglas($request, $curso->id), $this->mensajes());
        $cal   = $request->validate($this->reglasCalendario(false), $this->mensajesCalendario());

        $cambios = DB::transaction(function () use ($curso, $datos, $cal) {
            $antes = $curso->only(CalendarioCursoService::CAMPOS);
            $curso->update($datos);

            // Primero se ajustan los días que ya había (fechas y horario) y luego se añaden los nuevos
            $cambios = app(CalendarioCursoService::class)->ajustar($curso, $antes);
            $cambios['anadidos'] = $this->generarCalendario($curso, $cal);

            return $cambios;
        });

        return redirect()
            ->route('curso.crearTablaCurso')
            ->with('ok', 'Curso actualizado correctamente.' . $this->resumenCalendario($cambios));
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
            'aula'                    => 'nullable|string|max:45|required_unless:modalidad,TELEFORMACION',
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

    // " Calendario: 3 días añadidos, 2 actualizados." (vacío si no ha cambiado nada)
    private function resumenCalendario(array $c): string
    {
        $partes = array_filter([
            ($c['anadidos'] ?? 0) ? $c['anadidos'] . ($c['anadidos'] === 1 ? ' día añadido' : ' días añadidos') : null,
            ($c['actualizados'] ?? 0) ? $c['actualizados'] . ($c['actualizados'] === 1 ? ' día con el horario actualizado' : ' días con el horario actualizado') : null,
            ($c['quitados'] ?? 0) ? $c['quitados'] . ($c['quitados'] === 1 ? ' día quitado por estar fuera de las fechas' : ' días quitados por estar fuera de las fechas') : null,
        ]);

        return $partes ? ' Calendario: ' . implode(', ', $partes) . '.' : '';
    }

    // Campos del apartado "Calendario" del formulario de curso. No se guardan en la tabla curso:
    // solo dicen qué días crear en curso_calendario.
    //  - Al crear un curso siempre se genera; al editarlo, solo si se marca "generar_calendario".
    private function reglasCalendario(bool $creando): array
    {
        $generar = $creando ? '' : 'exclude_unless:generar_calendario,1|';

        return [
            'generar_calendario' => 'nullable|boolean',
            'calendario_modo'    => $generar . 'required|in:semana,alterno',
            'dias_semana'        => $generar . 'exclude_unless:calendario_modo,semana|required|array|min:1',
            'dias_semana.*'      => 'integer|between:1,7|distinct',
            // Segundo tramo: solo con turno partido, y entonces obligatorio
            'hora_inicio2'       => $generar . 'exclude_unless:turno,PARTIDO|required|date_format:H:i|after_or_equal:hora_final',
            'hora_final2'        => $generar . 'exclude_unless:turno,PARTIDO|required|date_format:H:i|after:hora_inicio2',
        ];
    }

    private function mensajesCalendario(): array
    {
        return [
            'calendario_modo.required'    => 'Elige qué días hay clase.',
            'dias_semana.required'        => 'Marca al menos un día de clase.',
            'dias_semana.min'             => 'Marca al menos un día de clase.',
            'hora_inicio2.required'       => 'En turno partido indica la hora de inicio del segundo tramo.',
            'hora_final2.required'        => 'En turno partido indica la hora de fin del segundo tramo.',
            'hora_inicio2.after_or_equal' => 'El segundo tramo debe empezar después de que acabe el primero.',
            'hora_final2.after'           => 'La hora final debe ser posterior a la de inicio.',
            'date_format'                 => 'La hora debe tener el formato HH:MM.',
        ];
    }

    // Crea los días del calendario con los datos validados del apartado "Calendario"
    private function generarCalendario(Curso $curso, array $cal): int
    {
        if (empty($cal['calendario_modo'])) {
            return 0; // al editar sin marcar "Añadir días al calendario"
        }

        $patron = ['modo' => $cal['calendario_modo'], 'dias' => $cal['dias_semana'] ?? []];

        return app(CalendarioCursoService::class)
            ->generar($curso, $patron, $cal['hora_inicio2'] ?? null, $cal['hora_final2'] ?? null);
    }

    // Datos de los desplegables del formulario de curso
    private function datosDesplegables(): array
    {
        $formaciones = Formacion::where('activo', true)->orderBy('nombre')->pluck('nombre', 'id');
        $centros     = Centro::orderBy('nombre')->pluck('nombre', 'id');

        $turnos = collect(Turno::cases())->mapWithKeys(
            fn($turno) => [$turno->value => ucfirst(strtolower($turno->value))]
        );

        // La modalidad ahora va en el curso (antes estaba en formación)
        $modalidades = collect(Modalidad::cases())->mapWithKeys(
            fn($m) => [$m->value => ucfirst(strtolower($m->value))]
        );

        return compact('formaciones', 'centros', 'turnos', 'modalidades');
    }

    // Texto identificativo del curso: "acción/grupo"
    private function codigoCurso(Curso $curso): string
    {
        return $curso->codigo_accion . '/' . $curso->codigo_grupo;
    }

    // Rol y nombre del usuario conectado adaptado a la nueva estructura con rol_persona
    private function datosUsuario(): array
    {
        $personaId = Auth::user()->persona_id;

        // Buscamos el rol a través de la tabla intermedia rol_persona
        $rol = DB::table('rol_persona')
            ->join('rol', 'rol.id', '=', 'rol_persona.rol_id')
            ->where('rol_persona.persona_id', $personaId)
            ->value('rol.rol'); // O ajusta según el nombre del campo del enum en tu tabla rol

        $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

        $nombre = collect([$persona?->nombre, $persona?->primer_apellido, $persona?->segundo_apellido])
            ->filter()
            ->implode(' ');

        return compact('rol', 'nombre');
    }

    // Elimina la relación del alumno con el curso (desmatricular)
    public function eliminarCursoAlumno($cursoId, $cursoAlumnoId)
    {
        $cursoAlumno = CursoAlumno::where('curso_id', $cursoId)
            ->where('id', $cursoAlumnoId)
            ->firstOrFail();

        $cursoAlumno->delete();

        return redirect()
            ->route('curso.visualizar', $curso)
            ->with('ok', 'Alumno desmatriculado del curso correctamente.');
    }

    // Genera las opciones únicas para el desplegable del filtro de cursos
    private function obtenerOpcionesFiltroCursos()
    {
        $acciones = Curso::whereNotNull('codigo_accion')->distinct()->pluck('codigo_accion', 'codigo_accion');
        $grupos = Curso::whereNotNull('codigo_grupo')->distinct()->pluck('codigo_grupo', 'codigo_grupo');
        $modalidades = Curso::whereNotNull('modalidad')->distinct()->pluck('modalidad', 'modalidad');
        
        // Empresas clientes vinculadas a través de los centros que tienen cursos
        $empresas = Empresa::whereIn(
            'id',
            Centro::whereIn('id', Curso::whereNotNull('centro_id')->select('centro_id'))->select('empresa_id')
        )->orderBy('nombre')->pluck('nombre', 'nombre');

        $formaciones = Formacion::whereHas('cursos')->orderBy('nombre')->pluck('nombre', 'nombre');

        return [
            'Acciones'          => $acciones->toArray(),
            'Grupos'            => $grupos->toArray(),
            'Modalidades'       => $modalidades->toArray(),
            'Empresas Clientes' => $empresas->toArray(),
            'Formaciones'       => $formaciones->toArray(),
        ];
    }
}
