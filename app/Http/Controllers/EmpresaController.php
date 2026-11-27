<?php

namespace App\Http\Controllers;

use App\Models\CursoAlumno;
use App\Models\Empresa;
use App\Models\Enums\RolEmpresa;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $rol     = Rol::principal($personaId);
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
        $rol = Rol::principal(Auth::user()->persona_id);
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
        $rolesDisponibles = RolEmpresa::cases();
        $rolesSeleccionados = [RolEmpresa::Cliente->value]; // Por defecto cliente

        return view('empresa.formulario', compact('rolesDisponibles', 'rolesSeleccionados'));
    }

    // Muestra el formulario de edición de la empresa
    public function vistaEditar($id)
    {
        $empresa = Empresa::with('roles')->findOrFail($id);
        $rolesDisponibles = RolEmpresa::cases();
        // Extraemos los valores de los roles que ya tiene asignados la empresa
        $rolesSeleccionados = $empresa->roles->pluck('rol_empresa')->toArray();

        return view('empresa.formulario', compact('empresa', 'rolesDisponibles', 'rolesSeleccionados'));
    }

    // Actualiza la empresa desde el formulario web
    public function actualizarEmpresaWeb(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);

        $datos = $request->validate($this->reglas($empresa->id));

        DB::transaction(function () use ($empresa, $datos) {
            $empresa->update(collect($datos)->except('roles')->all());
            // Los roles solo se cambian si vienen en la petición
            if (array_key_exists('roles', $datos)) {
                $this->sincronizarRoles($empresa, $datos['roles']);
            }
        });

        return redirect()
            ->route('empresa.index')
            ->with('ok', 'Empresa actualizada correctamente.');
    }

    // Elimina la empresa desde la tabla web
    public function eliminarEmpresaWeb($id)
    {
        $empresa = Empresa::findOrFail($id);

        try {
            DB::transaction(function () use ($empresa) {
                // Los roles no son datos: se quitan para que no impidan borrar la empresa
                $empresa->roles()->detach();
                $empresa->delete();
            });
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

    // Listar todas las empresas con sus roles (?rol=PROVEEDOR para filtrar)
    public function listarEmpresa(Request $request)
    {
        $empresas = Empresa::with('roles')
            ->when($request->filled('rol') && RolEmpresa::tryFrom($request->rol),
                fn ($q) => $q->conRol(RolEmpresa::from($request->rol)))
            ->orderBy('nombre')
            ->get();

        return response()->json($empresas);
    }

    // Mostrar una empresa en concreto
    public function obtenerEmpresaPorID($id)
    {
        $empresa = Empresa::with('roles')->find($id);

        if (! $empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada'], 404);
        }

        return response()->json($empresa);
    }

    // Crear una empresa (sirve tanto para la web como para la API)
    public function agregarEmpresa(Request $request)
    {
        $datos = $request->validate($this->reglas());

        $empresa = DB::transaction(function () use ($datos) {
            $empresa = Empresa::create(collect($datos)->except('roles')->all());
            // Si no se indican roles, se crea como CLIENTE (lo que eran todas hasta ahora)
            $this->sincronizarRoles($empresa, $datos['roles'] ?? [RolEmpresa::Cliente->value]);

            return $empresa;
        });

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

        $datos = $request->validate($this->reglas($empresa->id));

        DB::transaction(function () use ($empresa, $datos) {
            $empresa->update(collect($datos)->except('roles')->all());
            // Los roles solo se cambian si vienen en la petición
            if (array_key_exists('roles', $datos)) {
                $this->sincronizarRoles($empresa, $datos['roles']);
            }
        });

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
            DB::transaction(function () use ($empresa) {
                // Los roles no son datos: se quitan para que no impidan borrar la empresa
                $empresa->roles()->detach();
                $empresa->delete();
            });
            return response()->json(['mensaje' => 'Empresa eliminada']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: la empresa tiene datos relacionados',
            ], 409);
        }
    }

    /* =========================================================
     *  AUXILIARES
     * ========================================================= */

    private function reglas(?int $id = null): array
    {
        return [
            'cif'                => ['required', 'string', 'max:15', Rule::unique('empresa', 'cif')->ignore($id)],
            'nombre'             => 'required|string|max:100',
            'direccion'          => 'nullable|string|max:150',
            'codigo_postal'      => 'nullable|string|size:5',
            'municipio'          => 'nullable|string|max:45',
            'provincia'          => 'nullable|string|max:45',
            'email_facturacion'  =>  ['nullable','email','max:100','regex:/^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/'],
            'dias_vencimiento'   => 'sometimes|integer|min:0|max:365',
            'telefono'           => 'nullable|string|max:20',
            'total_trabajadores' => 'nullable|integer|min:0',
            'activo'             => 'sometimes|boolean',
            'roles'              => 'sometimes|array|min:1',
            'roles.*'            => ['distinct', Rule::enum(RolEmpresa::class)],
        ];
    }

    // Deja a la empresa exactamente con los roles indicados (tabla rol_empresa)
    private function sincronizarRoles(Empresa $empresa, array $roles): void
    {
        $empresa->roles()->sync(
            collect($roles)->map(fn ($rol) => Rol::idDeEmpresa($rol))->unique()->all()
        );
    }
}
