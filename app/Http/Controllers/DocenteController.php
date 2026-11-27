<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\CursoDocente;
use App\Models\DocenteCalendario;
use App\Models\DocenteTutoria;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\TipoRetribucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocenteController extends Controller
{
    // Listado con buscador y filtro por curso
    public function index(Request $request)
    {
        $this->soloAdministradorYCoordinador();

        $buscar  = trim((string) $request->query('buscar', ''));
        $cursoId = $request->integer('curso') ?: null;
        $hoy     = today()->toDateString();

        $docentes = Persona::query()
            ->whereIn('id', CursoDocente::select('persona_id')->when($cursoId, fn ($q) => $q->where('curso_id', $cursoId)))
            ->when($buscar !== '', function ($q) use ($buscar) {
                $like = '%' . $buscar . '%';
                $q->where(fn ($w) => $w
                    ->where('nombre', 'like', $like)
                    ->orWhere('primer_apellido', 'like', $like)
                    ->orWhere('segundo_apellido', 'like', $like)
                    ->orWhereRaw("CONCAT_WS(' ', nombre, primer_apellido, segundo_apellido) LIKE ?", [$like])
                    ->orWhere('DNI', 'like', $like)
                    ->orWhere('NIE', 'like', $like)
                    ->orWhere('email', 'like', $like));
            })
            ->withCount(['cursoDocentes as cursos_count'])
            ->withCount(['cursoDocentes as en_marcha_count' => fn ($q) => $q->whereDate('fecha_inicio', '<=', $hoy)->whereDate('fecha_fin', '>=', $hoy)])
            ->withSum('cursoDocentes as horas_total', 'numero_horas')
            ->orderBy('primer_apellido')->orderBy('segundo_apellido')->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $cursos = Curso::with('formacion')
            ->whereIn('id', CursoDocente::select('curso_id'))
            ->orderByDesc('fecha_inicio')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => $c->codigo_accion . '/' . $c->codigo_grupo . ' · ' . ($c->formacion?->nombre ?? 'Sin formación')]);

        return view('docente.index', compact('docentes', 'cursos', 'buscar', 'cursoId'));
    }

    // Ficha de un docente: datos, cursos que imparte, horario, tutorías y retribución
    public function visualizar(Persona $persona)
    {
        $this->soloAdministradorYCoordinador();

        $asignaciones = CursoDocente::where('persona_id', $persona->id)
            ->with(['curso.formacion', 'empresa'])
            ->orderByDesc('fecha_inicio')
            ->get();

        // Si no imparte ningún curso no es docente
        abort_if($asignaciones->isEmpty(), 404);

        $ids = $asignaciones->pluck('id');

        // Agrupados por asignación (curso_docente_id) para pintarlos con su curso
        $horarios      = DocenteCalendario::whereIn('curso_docente_id', $ids)->get()->groupBy('curso_docente_id');
        $tutorias      = DocenteTutoria::whereIn('curso_docente_id', $ids)->get()->groupBy('curso_docente_id');
        $retribuciones = TipoRetribucion::whereIn('curso_docente_id', $ids)->orderByDesc('fecha')->get()->groupBy('curso_docente_id');

        return view('docente.visualizar', compact('persona', 'asignaciones', 'horarios', 'tutorias', 'retribuciones'));
    }

    private function soloAdministradorYCoordinador(): void
    {
        $rol = Rol::principal(Auth::user()->persona_id);
        abort_unless(in_array($rol, ['ADMINISTRADOR', 'COORDINADOR'], true), 403, 'No tienes acceso a los datos de los docentes.');
    }
}