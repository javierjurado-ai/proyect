<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Enums\Genero;
use App\Models\Enums\LOPD;
use App\Models\Enums\TipoRol;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class PersonaController extends Controller
{
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

        $personas = Persona::all();

        return view('persona.index', compact('personas', 'rol', 'nombre'));
    }
    /* =========================================================
     *  FORMULARIO (web)
     * ========================================================= */

    // Muestra el formulario de alta
    public function create(Request $request)
    {
        $personaId = Auth::user()->persona_id;

        $rol     = Rol::principal($personaId);
        $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);
        $nombre = collect([$persona->nombre, $persona->primer_apellido, $persona->segundo_apellido])
            ->filter()
            ->implode(' ');
        if (! $rol || ! $persona) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['usuario' => 'No tienes acceso a la página.']);
        }
        // [valor => texto] para el desplegable de género, sacado del enum
        $generos = collect(Genero::cases())
            ->mapWithKeys(fn($g) => [$g->value => ucfirst(strtolower($g->value))]);
        $lopd = collect(LOPD::cases())
            ->mapWithKeys(fn($l) => [$l->value => ucfirst(strtolower($l->value))]);

        // [id => nombre] de la tabla empresa para el desplegable
        $empresas = Empresa::orderBy('nombre')->pluck('nombre', 'id');
        $volver = $request->old('volver', url()->previous());
        return view('persona.formulario', compact('generos', 'empresas', 'lopd', 'rol', 'nombre','volver'));
    }

    // Guarda lo enviado desde el formulario y vuelve con mensaje
    public function store(Request $request)
    {
        $this->normalizarDocumento($request);

        $datos = $request->validate($this->reglas(), $this->mensajes());

        Persona::create($datos);

        return redirect()
            ->route('persona.create')
            ->with('ok', 'Persona guardada correctamente.');
    }

    // Muestra el formulario con los datos de la persona para editarla
    public function editarFormulario(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);

        $generos = collect(Genero::cases())
            ->mapWithKeys(fn($g) => [$g->value => ucfirst(strtolower($g->value))]);
        $lopd = collect(LOPD::cases())
            ->mapWithKeys(fn($l) => [$l->value => ucfirst(strtolower($l->value))]);

        // Al volver de un error de validación se conserva a dónde había que regresar
        $volver = $request->old('volver', url()->previous());

        return view('persona.formulario', compact('persona', 'generos', 'lopd', 'volver'));
    }

    // Guarda los cambios del formulario de edición y vuelve a donde estaba
    public function update(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);

        $this->normalizarDocumento($request);

        $datos = $request->validate($this->reglas(), $this->mensajes());

        $persona->update($datos);

        return redirect($this->urlVolver($request, route('persona.index')))
            ->with('ok', 'Persona actualizada correctamente.');
    }
    /* =========================================================
     *  API (JSON)
     * ========================================================= */

    public function crearRol(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);

        $datos = $request->validate([
            'rol' => ['required', Rule::enum(TipoRol::class)],
        ], [
            'rol.required' => 'Debes seleccionar un rol.',
            'rol.enum'     => 'El rol seleccionado no es válido.',
        ]);

        // Se añade a los que ya tenga (tabla rol_persona); no se repite
        $persona->roles()->syncWithoutDetaching([Rol::idDePersona($datos['rol'])]);

        return back()->with('ok', 'Rol asignado correctamente.');
    }

    public function listarPersona()
    {
        return response()->json(Persona::all());
    }

    public function obtenerUsuarioPorID($id)
    {
        $persona = Persona::findOrFail($id);

        return response()->json($persona);
    }

    public function agregarPersona(Request $request)
    {
        $this->normalizarDocumento($request);

        $datos = $request->validate($this->reglas(), $this->mensajes());

        Persona::create($datos);

        return redirect($this->urlVolver($request, route('persona.index')))
            ->with('ok', 'Persona guardada correctamente.');
    }
    public function agregarPersonaPorAlumno(Request $request, Curso $curso)
    {
        $this->normalizarDocumento($request);

        $datos = $request->validate($this->reglas(), $this->mensajes());

        Persona::create($datos);

        return redirect("/curso/{$curso->id}/alumnos")
            ->with('ok', 'Persona guardada correctamente.');
    }

    public function actualizarPersona(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);

        $this->normalizarDocumento($request);

        $datos = $request->validate($this->reglas(), $this->mensajes());

        $persona->update($datos);

        return response()->json($persona, 200);
    }

    public function eliminarPersona($id)
    {
        $persona = Persona::findOrFail($id);

        $persona->delete();

        return response()->json(['mensaje' => 'Persona eliminada'], 200);
    }

    /* =========================================================
     *  AUXILIARES (compartidos por formulario y API)
     * ========================================================= */

    // URL a la que volver tras guardar (campo oculto "volver" del formulario).
    // Solo se aceptan URLs de este mismo sitio, para evitar redirecciones a webs externas.
    private function urlVolver(Request $request, string $porDefecto): string
    {
        $volver = $request->input('volver');
        $host   = parse_url((string) $volver, PHP_URL_HOST);

        $esInterna = is_string($volver)
            && $volver !== ''
            && ($host === null || $host === $request->getHost())
            && ! str_starts_with($volver, '//');

        return $esInterna ? $volver : $porDefecto;
    }

    // DNI/NIE en mayúsculas y sin espacios; vacío => null
    private function normalizarDocumento(Request $request): void
    {
        $request->merge([
            'DNI' => $request->filled('DNI') ? strtoupper(trim($request->DNI)) : null,
            'NIE' => $request->filled('NIE') ? strtoupper(trim($request->NIE)) : null,
        ]);
    }

    private function reglas(): array
    {
        return [
            'nombre'                  => 'required|string|max:45',
            'primer_apellido'         => 'required|string|max:45',
            'segundo_apellido'        => 'nullable|string|max:45',
            'fecha_nacimiento'        => 'required|date|before_or_equal:today',
            'genero'                  => ['required', Rule::enum(Genero::class)],
            'nacionalidad'            => 'required|string|max:45',
            'telefono'                => 'required|string|max:20',
            'direccion'               => 'required|string|max:150',
            'email'                   => 'required|email|max:100',
            // Solo uno de los dos: DNI o NIE
            'DNI'                     => ['nullable', 'required_without:NIE', 'prohibits:NIE', 'max:15', 'regex:/^\d{8}[A-Z]$/'],
            'NIE'                     => ['nullable', 'required_without:DNI', 'max:15', 'regex:/^[XYZ]\d{7}[A-Z]$/'],
            'numero_seguridad_social' => 'required|string|max:20',
            'LOPD'                    => ['required', Rule::enum(LOPD::class)],
        ];
    }

    private function mensajes(): array
    {
        return [
            'required'                         => 'Este campo es obligatorio.',
            'max'                              => 'Máximo :max caracteres.',
            'email'                            => 'El email no es válido.',
            'date'                             => 'La fecha no es válida.',
            'enum'                             => 'El valor elegido no es válido.',
            'empresa_id.exists'                => 'La empresa elegida no existe.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'DNI.required_without'             => 'Debes indicar el DNI o el NIE.',
            'NIE.required_without'             => 'Debes indicar el DNI o el NIE.',
            'DNI.prohibits'                    => 'Indica solo el DNI o el NIE, no los dos.',
            'DNI.regex'                        => 'El DNI debe tener 8 números y una letra.',
            'NIE.regex'                        => 'El NIE debe empezar por X, Y o Z, seguido de 7 números y una letra.',
        ];
    }
}