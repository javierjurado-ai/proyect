<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Centro;
use App\Models\ComunicacionFundae;
use App\Models\CreditoFormativo;
use App\Models\Curso;
use App\Models\Empresa;
use App\Models\Formacion;
use App\Models\Persona;
use App\Models\PeticionesLogistica;
use App\Models\Presupuesto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InicioController extends Controller
{
    public function mostrarPaginaInicio(Request $request)
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

        // Cada rol tiene su propio inicio: views/<rol>/index.blade.php
        return match ($rol) {
            'ADMINISTRADOR' => view('administrador.index', array_merge(
                compact('rol', 'nombre'),
                ['primerNombre' => $persona->nombre],
                $this->datosAdministrador()
            )),
            'COORDINADOR' => view('coordinador.index', array_merge(
                compact('rol', 'nombre'),
                ['primerNombre' => $persona->nombre],
                $this->datosCoordinador()
            )),
            'COMERCIAL' => view('comercial.index', array_merge(
                compact('rol', 'nombre'),
                ['primerNombre' => $persona->nombre],
                $this->datosComercial()
            )),
            default => view('panel.inicio', compact('rol', 'nombre')),
        };
    }

    /**
     * Datos del inicio del ADMINISTRADOR: resumen de toda la plataforma.
     */
    private function datosAdministrador(): array
    {
        $hoy  = today();
        $anio = $hoy->year;

        // Cursos
        $cursosEnMarcha = Curso::whereDate('fecha_inicio', '<=', $hoy)
            ->whereDate('fecha_fin', '>=', $hoy)
            ->count();

        $cursosEstaSemana = Curso::whereBetween('fecha_inicio', [
            $hoy->copy()->startOfWeek()->toDateString(),
            $hoy->copy()->endOfWeek()->toDateString(),
        ])->count();

        // Crédito del año
        $creditosAnio      = CreditoFormativo::with('empresa')->where('año', $anio)->get();
        $creditoTotal      = (float) $creditosAnio->sum('importe_total');
        $creditoConsumido  = (float) $creditosAnio->sum('importe_consumido');
        $porcentajeCredito = $creditoTotal > 0 ? round(100 * $creditoConsumido / $creditoTotal) : 0;

        $creditoPorEmpresa = $creditosAnio
            ->sortByDesc(fn ($c) => (float) $c->importe_total)
            ->take(6)
            ->map(fn ($c) => [
                'empresa'    => $c->empresa?->nombre ?? 'Empresa #' . $c->empresa_id,
                'consumido'  => (float) $c->importe_consumido,
                'total'      => (float) $c->importe_total,
                'porcentaje' => (float) $c->importe_total > 0
                    ? round(100 * (float) $c->importe_consumido / (float) $c->importe_total)
                    : 0,
            ])
            ->values();

        // FUNDAE y logística
        $fundaePendientes = ComunicacionFundae::whereNull('fecha_envio')->count();
        $fundaeVencidas   = ComunicacionFundae::whereNull('fecha_envio')
            ->whereDate('fecha_limite', '<', $hoy)
            ->count();
        $logisticaPendiente = PeticionesLogistica::where('estado', 'PENDIENTE')->count();

        // Personas por rol
        $rolesDeAcceso      = ['ADMINISTRADOR', 'COORDINADOR', 'COMERCIAL'];
        $personasConUsuario = Usuario::pluck('persona_id');

        // Cosas que revisar
        $personasSinUsuario = Rol::whereIn('rol', $rolesDeAcceso)
            ->whereNotIn('persona_id', $personasConUsuario)
            ->distinct()
            ->count('persona_id');

        $usuariosSinRol = Usuario::whereNotIn('persona_id', Rol::pluck('persona_id'))->count();

        $empresasSinCredito = Empresa::whereNotIn('id', $creditosAnio->pluck('empresa_id'))
            ->orderBy('nombre')
            ->pluck('nombre');

        return [
            'anio'               => $anio,
            'totalUsuarios'      => Usuario::count(),
            'cursosEnMarcha'     => $cursosEnMarcha,
            'cursosEstaSemana'   => $cursosEstaSemana,
            'totalEmpresas'      => Empresa::count(),
            'creditoTotal'       => $creditoTotal,
            'creditoConsumido'   => $creditoConsumido,
            'porcentajeCredito'  => $porcentajeCredito,
            'creditoPorEmpresa'  => $creditoPorEmpresa,

            'totalCursos'        => Curso::count(),
            'totalFormaciones'   => Formacion::count(),
            'totalAulas'         => Aula::count(),
            'totalCentros'       => Centro::count(),
            'totalAlumnos'       => Rol::where('rol', 'ALUMNO')->distinct()->count('persona_id'),
            'totalDocentes'      => Rol::where('rol', 'DOCENTE')->distinct()->count('persona_id'),

            'fundaePendientes'   => $fundaePendientes,
            'fundaeVencidas'     => $fundaeVencidas,
            'logisticaPendiente' => $logisticaPendiente,
            'totalPresupuestos'  => Presupuesto::count(),

            'totalPersonal'      => Rol::whereIn('rol', $rolesDeAcceso)->distinct()->count('persona_id'),
            'totalCreditos'      => $creditosAnio->count(),

            'personasSinUsuario' => $personasSinUsuario,
            'usuariosSinRol'     => $usuariosSinRol,
            'empresasSinCredito' => $empresasSinCredito,
        ];
    }

    /**
     * Datos del inicio del COORDINADOR: lo urgente del día a día.
     */
    private function datosCoordinador(): array
    {
        $hoy          = today();
        $inicioSemana = $hoy->copy()->startOfWeek();               // lunes
        $finSemana    = $hoy->copy()->startOfWeek()->addDays(4);   // viernes
        $dentroDe7    = $hoy->copy()->addDays(7);

        $nombreCurso = fn ($curso) => $curso?->formacion?->nombre ?? ('Curso ' . ($curso?->codigo_curso ?? ''));

        // --- Indicadores ---
        $cursosEnMarcha = Curso::whereDate('fecha_inicio', '<=', $hoy)
            ->whereDate('fecha_fin', '>=', $hoy)
            ->count();

        $cursosEstaSemana = Curso::whereBetween('fecha_inicio', [
            $inicioSemana->toDateString(),
            $hoy->copy()->endOfWeek()->toDateString(),
        ])->count();

        $fundaePendientes = ComunicacionFundae::whereNull('fecha_envio')->count();
        $fundaeUrgentes   = ComunicacionFundae::whereNull('fecha_envio')
            ->whereDate('fecha_limite', '<=', $hoy->copy()->addDays(3))
            ->count();

        $logisticaPendiente = PeticionesLogistica::where('estado', 'PENDIENTE')->count();

        // Aulas (consulta directa: solo necesitamos los textos)
        $aulas = DB::table('aulas')
            ->select('nombre_aula', 'capacidad', 'estado', 'uso_aula')
            ->orderBy('nombre_aula')
            ->get();
        $aulasLibres = $aulas->where('estado', 'DISPONIBLE')->count();

        // --- Requiere tu atención ---
        $tareas = [];

        // 1. Comunicaciones FUNDAE sin enviar, de la más urgente a la menos
        $textosTipo = [
            'INICIO'       => 'Comunicar inicio',
            'MODIFICACION' => 'Comunicar modificación',
            'FINALIZACION' => 'Comunicar finalización',
            'CANCELACION'  => 'Comunicar cancelación',
        ];

        ComunicacionFundae::with('curso.formacion')
            ->whereNull('fecha_envio')
            ->orderBy('fecha_limite')
            ->limit(5)
            ->get()
            ->each(function ($c) use (&$tareas, $hoy, $textosTipo, $nombreCurso) {
                $tipo   = $c->tipo instanceof \BackedEnum ? $c->tipo->value : $c->tipo;
                $limite = $c->fecha_limite ? \Illuminate\Support\Carbon::parse($c->fecha_limite) : null;

                $vencida = $limite && $limite->lt($hoy);
                $esHoy   = $limite && $limite->isSameDay($hoy);

                $detalle = ! $limite ? 'Sin fecha límite'
                    : ($vencida ? 'Venció el ' . $limite->format('d/m') . ' · ¡vencida!'
                    : ($esHoy ? 'Fecha límite: hoy'
                    : 'Fecha límite: ' . $limite->format('d/m/Y')));

                $tareas[] = [
                    'etiqueta' => 'FUNDAE',
                    'color'    => ($vencida || $esHoy) ? 'rojo' : 'ambar',
                    'titulo'   => ($textosTipo[$tipo] ?? 'Comunicación') . ' · ' . $nombreCurso($c->curso),
                    'detalle'  => $detalle,
                    'accion'   => 'Comunicar',
                    'ruta'     => 'fundae.index',
                ];
            });

        // 2. Cursos que empiezan en los próximos 7 días con algo sin asignar
        $proximos = Curso::with('formacion')
            ->whereDate('fecha_inicio', '>=', $hoy)
            ->whereDate('fecha_inicio', '<=', $dentroDe7)
            ->orderBy('fecha_inicio')
            ->get();

        $conDocente = DB::table('curso_docente')->pluck('curso_id')->all();
        $conAlumnos = DB::table('curso_alumno')->pluck('curso_id')->all();

        foreach ($proximos as $curso) {
            $empieza = 'Empieza el ' . $curso->fecha_inicio->locale('es')->isoFormat('dddd D/M');

            if (! $curso->aula && ! $curso->aula_virtual) {
                $tareas[] = ['etiqueta' => 'Sin aula', 'color' => 'ambar', 'titulo' => $nombreCurso($curso),
                    'detalle' => $empieza, 'accion' => 'Asignar aula', 'ruta' => 'curso.crearTablaCurso'];
            }
            if (! in_array($curso->id, $conDocente)) {
                $tareas[] = ['etiqueta' => 'Sin docente', 'color' => 'ambar', 'titulo' => $nombreCurso($curso),
                    'detalle' => $empieza, 'accion' => 'Asignar docente', 'ruta' => 'curso.crearTablaCurso'];
            }
            if (! in_array($curso->id, $conAlumnos)) {
                $tareas[] = ['etiqueta' => 'Sin alumnos', 'color' => 'ambar', 'titulo' => $nombreCurso($curso),
                    'detalle' => $empieza, 'accion' => 'Añadir alumnos', 'ruta' => 'curso.crearTablaCurso'];
            }
        }

        // 3. Logística
        if ($logisticaPendiente) {
            $tareas[] = [
                'etiqueta' => 'Logística',
                'color'    => 'azul',
                'titulo'   => $logisticaPendiente . ($logisticaPendiente === 1 ? ' petición pendiente' : ' peticiones pendientes'),
                'detalle'  => 'Material o equipamiento por preparar',
                'accion'   => 'Revisar',
                'ruta'     => 'logistica.index',
            ];
        }

        // --- Agenda de la semana (lunes a viernes) ---
        $cursosSemana = Curso::with('formacion')
            ->whereDate('fecha_inicio', '<=', $finSemana)
            ->whereDate('fecha_fin', '>=', $inicioSemana)
            ->orderBy('hora_inicio')
            ->get();

        $semana = [];
        for ($d = $inicioSemana->copy(); $d->lte($finSemana); $d->addDay()) {
            $dia = $d->copy();
            $semana[] = [
                'titulo' => ucfirst(str_replace('.', '', $dia->locale('es')->isoFormat('ddd D'))),
                'esHoy'  => $dia->isSameDay($hoy),
                'cursos' => $cursosSemana
                    ->filter(fn ($c) => $c->fecha_inicio->lte($dia) && $c->fecha_fin->gte($dia))
                    ->map(fn ($c) => [
                        'nombre' => $nombreCurso($c),
                        'hora'   => $c->hora_inicio
                            ? substr($c->hora_inicio, 0, 5) . ($c->hora_final ? '–' . substr($c->hora_final, 0, 5) : '')
                            : 'Sin horario',
                        'lugar'  => $c->aula ?: ($c->aula_virtual ? 'Aula virtual' : 'Sin aula'),
                        'tarde'  => ($c->turno instanceof \BackedEnum ? $c->turno->value : $c->turno) === 'TARDE',
                    ])
                    ->values(),
            ];
        }

        return [
            'cursosEnMarcha'     => $cursosEnMarcha,
            'cursosEstaSemana'   => $cursosEstaSemana,
            'fundaePendientes'   => $fundaePendientes,
            'fundaeUrgentes'     => $fundaeUrgentes,
            'logisticaPendiente' => $logisticaPendiente,
            'aulas'              => $aulas,
            'aulasLibres'        => $aulasLibres,
            'tareas'             => $tareas,
            'semana'             => $semana,
        ];
    }

    /**
     * Datos del inicio del COMERCIAL: empresas, presupuestos y catálogo.
     */
    private function datosComercial(): array
    {
        $hoy = today();

        return [
            'anio'              => $hoy->year,
            'diasParaFinDeAnio' => (int) $hoy->diffInDays($hoy->copy()->endOfYear()),

            'totalEmpresas'     => Empresa::count(),
            'totalPresupuestos' => Presupuesto::count(),
            'importeTotal'      => (float) Presupuesto::sum('importe_bonificado'),
            'totalFormaciones'  => Formacion::where('activo', true)->count(),

            'presupuestos'      => Presupuesto::orderByDesc('id')->limit(5)->get(),
            'empresas'          => Empresa::orderByDesc('id')->limit(5)->get(),
            'destacadas'        => Formacion::where('activo', true)->orderByDesc('id')->limit(3)->get(),
        ];
    }
}