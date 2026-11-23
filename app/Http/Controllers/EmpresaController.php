<?php

namespace App\Http\Controllers;

use App\Models\CursoAlumno;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
     /* =========================================================
     *  FORMULARIO Y TABLA (web)
     * ========================================================= */

    // Muestra el listado de empresas (vista empresa/index.blade.php)
    public function index(Request $request)
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

        $empresas = Empresa::orderBy('nombre')->paginate(15);

        return view('empresa.index', compact('empresas', 'rol', 'nombre'));
    }

    // Ficha de la empresa: datos, cursos, créditos, centros, costes y alumnos
    public function visualizarEmpresa(Empresa $empresa)
    {
        $empresa->load([
            'centros',
            'creditosFormativos' => fn ($q) => $q->orderByDesc('año'),
            'cursosGrupo'        => fn ($q) => $q->with('formacion')->orderByDesc('fecha_inicio'),
            'costes.curso.formacion',
            'informesRlt',
        ]);

        // Los datos personales de los alumnos solo los ven administrador y coordinador
        $rol = Rol::where('persona_id', Auth::user()->persona_id)->value('rol')?->value;
        $puedeVerAlumnos = in_array($rol, ['ADMINISTRADOR', 'COORDINADOR'], true);

        $alumnos = $puedeVerAlumnos
            ? CursoAlumno::where('empresa_id', $empresa->id)->with(['persona', 'curso.formacion'])->get()
                ->sortBy(fn ($a) => $a->persona?->primer_apellido . ' ' . $a->persona?->nombre)->values()
            : collect();

        // Crédito del año en curso
        $creditoActual = $empresa->creditosFormativos->firstWhere('año', today()->year);

        return view('empresa.visualizar', compact('empresa', 'alumnos', 'puedeVerAlumnos', 'creditoActual'));
    }

    // Mostrar la vista del formulario de creación web
    public function mostrarFormulario()
    {
        return view('empresa.formulario');
    }

    // Muestra el formulario de edición de la empresa
    public function vistaEditar($id)
    {
        $empresa = Empresa::findOrFail($id);

        return view('empresa.formulario', compact('empresa'));
    }

    // Actualiza la empresa desde el formulario web
    public function actualizarEmpresaWeb(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);

        $datos = $request->validate([
            'cif'                => ['required', 'string', 'max:15', Rule::unique('empresa', 'cif')->ignore($empresa->id)],
            'nombre'             => 'required|string|max:100',
            'total_trabajadores' => 'nullable|integer|min:0',
        ]);

        $empresa->update($datos);

        return redirect()
            ->route('empresa.index')
            ->with('ok', 'Empresa actualizada correctamente.');
    }

    // Elimina la empresa desde la tabla web
    public function eliminarEmpresaWeb($id)
    {
        $empresa = Empresa::findOrFail($id);

        try {
            $empresa->delete();
            return redirect()
                ->route('empresa.index')
                ->with('ok', 'Empresa eliminada correctamente.');
        } catch (QueryException $e) {
            return redirect()
                ->route('empresa.index')
                ->with('error', 'No se puede eliminar: la empresa tiene centros o datos relacionados.');
        }
    }

    /* =========================================================
     *  API REST
     * ========================================================= */

    // Listar todas las empresas
    public function listarEmpresa()
    {
        return response()->json(Empresa::all());
    }

    // Mostrar una empresa en concreto
    public function obtenerEmpresaPorID($id)
    {
        $empresa = Empresa::find($id);

        if (! $empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada'], 404);
        }

        return response()->json($empresa);
    }

    // Crear una empresa (sirve tanto para la web como para la API)
    public function agregarEmpresa(Request $request)
    {
        $datos = $request->validate([
            'cif'                => 'required|string|max:15|unique:empresa,cif',
            'nombre'             => 'required|string|max:100',
            'total_trabajadores' => 'nullable|integer|min:0',
        ]);

        $empresa = Empresa::create($datos);

        // Si la petición espera JSON (Postman, fetch, etc.), devuelve el JSON
        if ($request->expectsJson()) {
            return response()->json($empresa, 201);
        }

        // Si viene del formulario web, vuelve a la tabla con mensaje
        return redirect()
            ->route('empresa.index')
            ->with('ok', 'Empresa guardada correctamente.');
    }

    // Actualizar una empresa
    public function actualizarEmpresa(Request $request, $id)
    {
        $empresa = Empresa::find($id);

        if (! $empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada'], 404);
        }

        $datos = $request->validate([
            'cif'                => ['required', 'string', 'max:15', Rule::unique('empresa', 'cif')->ignore($empresa->id)],
            'nombre'             => 'required|string|max:100',
            'total_trabajadores' => 'nullable|integer|min:0',
        ]);

        $empresa->update($datos);

        return response()->json($empresa);
    }

    // Eliminar una empresa
    public function eliminarEmpresa($id)
    {
        $empresa = Empresa::find($id);

        if (! $empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada'], 404);
        }

        try {
            $empresa->delete();
            return response()->json(['mensaje' => 'Empresa eliminada']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: la empresa tiene datos relacionados',
            ], 409);
        }
    }
}