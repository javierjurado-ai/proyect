<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\CursoAlumno;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Alumnos: personas apuntadas a algún curso (tabla curso_alumno).
 * Solo para administrador y coordinador.
 */
class AlumnoController extends Controller
{
    // Listado con buscador y filtro por curso
    public function index(Request $request)
    {
        $this->soloAdministradorYCoordinador();

        $buscar  = trim((string) $request->query('buscar', ''));
        $cursoId = $request->integer('curso') ?: null;

        $alumnos = Persona::query()
            // Solo las personas que están en curso_alumno (del curso elegido, si hay filtro)
            ->whereIn('id', CursoAlumno::select('persona_id')->when($cursoId, fn ($q) => $q->where('curso_id', $cursoId)))
            ->when($buscar !== '', fn ($q) => $this->filtroBusqueda($q, $buscar))
            ->withCount(['cursoAlumno as cursos_count'])
            ->withCount(['cursoAlumno as diplomas_count' => fn ($q) => $q->where('diploma', 'SI')])
            ->with(['empresasComoAlumno:id,nombre'])
            ->orderBy('primer_apellido')->orderBy('segundo_apellido')->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $cursos = $this->cursosParaFiltro(CursoAlumno::class);

        return view('alumno.index', compact('alumnos', 'cursos', 'buscar', 'cursoId'));
    }

    // Ficha de un alumno: datos personales y sus cursos
    public function visualizar(Persona $persona)
    {
        $this->soloAdministradorYCoordinador();

        $inscripciones = CursoAlumno::where('persona_id', $persona->id)
            ->with(['curso.formacion', 'empresa'])
            ->get()
            ->sortByDesc(fn ($i) => $i->curso?->fecha_inicio)
            ->values();

        // Si no está en ningún curso no es alumno
        abort_if($inscripciones->isEmpty(), 404);

        return view('alumno.visualizar', compact('persona', 'inscripciones'));
    }

    /* ---------------- Auxiliares ---------------- */

    private function soloAdministradorYCoordinador(): void
    {
        $rol = Rol::where('persona_id', Auth::user()->persona_id)->value('rol')?->value;
        abort_unless(in_array($rol, ['ADMINISTRADOR', 'COORDINADOR'], true), 403, 'No tienes acceso a los datos de los alumnos.');
    }

    // Nombre, apellidos, nombre completo, DNI, NIE o email
    private function filtroBusqueda($q, string $buscar)
    {
        $like = '%' . $buscar . '%';

        return $q->where(fn ($w) => $w
            ->where('nombre', 'like', $like)
            ->orWhere('primer_apellido', 'like', $like)
            ->orWhere('segundo_apellido', 'like', $like)
            ->orWhereRaw("CONCAT_WS(' ', nombre, primer_apellido, segundo_apellido) LIKE ?", [$like])
            ->orWhere('DNI', 'like', $like)
            ->orWhere('NIE', 'like', $like)
            ->orWhere('email', 'like', $like));
    }

    // [id => "acción/grupo · formación"] de los cursos que tienen alumnos
    private function cursosParaFiltro(string $tabla)
    {
        return Curso::with('formacion')
            ->whereIn('id', $tabla::select('curso_id'))
            ->orderByDesc('fecha_inicio')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => $c->codigo_accion . '/' . $c->codigo_grupo . ' · ' . ($c->formacion?->nombre ?? 'Sin formación')]);
    }
}