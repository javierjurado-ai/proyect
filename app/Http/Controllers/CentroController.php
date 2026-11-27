<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use App\Models\Empresa;
use App\Models\Enums\TipoCentro;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\Enums\TipoRol;
use App\Models\Rol;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Enums\Contacto;
use App\Models\CentroContactos;

class CentroController extends Controller
{
    /* =========================================================
     *  FORMULARIO Y TABLA (web)
     * ========================================================= */

    // Muestra el listado de centros (vista centro/index.blade.php)
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

        $centros = Centro::with('empresa')->orderBy('nombre')->paginate(15);

        return view('centro.index', compact('centros', 'rol', 'nombre'));
    }

    // Muestra el formulario de alta
    public function crearFormulario()
    {
        return view('centro.formulario', array_merge(
            $this->desplegables(),
            $this->datosUsuario()
        ));
    }

    // Guarda lo enviado desde el formulario y vuelve a la tabla
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        // numero_total_aula es NOT NULL en la BD y el formulario no lo envía
        $datos['numero_total_aula'] ??= 0;

        Centro::create($datos);

        return redirect()
            ->route('centro.index')
            ->with('ok', 'Centro guardado correctamente.');
    }

    // Muestra el formulario de edición con los datos del centro
    public function editarCentroFormulario($id)
    {
        $centro = Centro::findOrFail($id);

        return view('centro.formulario', array_merge(
            compact('centro'),
            $this->desplegables(),
            $this->datosUsuario()
        ));
    }

    // Actualiza el centro desde el formulario web
    public function actualizarCentroWeb(Request $request, $id)
    {
        $centro = Centro::findOrFail($id);

        $datos = $request->validate($this->reglas(), $this->mensajes());
        $datos['numero_total_aula'] ??= $centro->numero_total_aula ?? 0;

        $centro->update($datos);

        return redirect()
            ->route('centro.index')
            ->with('ok', 'Centro actualizado correctamente.');
    }

    // Elimina el centro desde la tabla web
    public function eliminarCentroWeb($id)
    {
        $centro = Centro::findOrFail($id);

        try {
            $centro->delete();
            return redirect()
                ->route('centro.index')
                ->with('ok', 'Centro eliminado correctamente.');
        } catch (QueryException $e) {
            return redirect()
                ->route('centro.index')
                ->with('error', 'No se puede eliminar: el centro tiene cursos o contactos asociados.');
        }
    }

    // Asigna el rol COORDINADOR a una persona (ruta POST /rol/{id})
    public function agregarRolCoordinador($id)
    {
        $persona = Persona::findOrFail($id);

        // Se añade a los roles que ya tenga (tabla rol_persona); no se repite si ya lo era
        $persona->roles()->syncWithoutDetaching([Rol::idDePersona(TipoRol::Coordinador)]);

        return response()->json($persona->roles()->get(), 201);
    }

    /* =========================================================
     *  VISTA DETALLADA DE CENTRO (web)
     * ========================================================= */

    // Muestra la vista con pestañas (Información y Aulas) de un centro
    public function visualizarCentro(Request $request, Centro $centro)
    {
        $centro->load(['empresa', 'contactos']);
        
        $datosUsuario = $this->datosUsuario();
        $rol = $datosUsuario['rol'];
        $nombre = $datosUsuario['nombre'];

        return view('centro.visualizar', compact('centro', 'rol', 'nombre'));
    }

    // Devuelve la sub-tabla de aulas asociadas a este centro (para la pestaña "Aulas")
    public function crearTablaPorAula($id)
    {
        $centro = Centro::with('aulas.curso.formacion')->findOrFail($id);
        $aulas = $centro->aulas;

        return view('centro.aulas-tabla', compact('aulas', 'centro'));
    }

    /* =========================================================
     *  FORMULARIO DE CONTACTOS DE CENTRO (web)
     * ========================================================= */

    // Muestra el formulario de alta de contacto de centro
    public function crearContactoFormulario()
    {
        $centros = Centro::orderBy('nombre')->pluck('nombre', 'id');

        $tiposContacto = collect(Contacto::cases())
            ->mapWithKeys(fn($c) => [$c->value => ucfirst(strtolower($c->value))]);

        return view('centro_contactos.formulario', compact('centros', 'tiposContacto'));
    }

    // Guarda el contacto enviado desde el formulario y vuelve con mensaje
    public function storeContacto(Request $request)
    {
        $datos = $request->validate($this->reglasContacto(), $this->mensajes());

        CentroContactos::create($datos);

        return redirect()
            ->route('centro_contactos.create')
            ->with('success', 'Contacto del centro guardado correctamente.');
    }

    /* =========================================================
     *  API (JSON)
     * ========================================================= */

    // Listar todos los centros
    public function listarCentro()
    {
        return response()->json(Centro::all());
    }

    // Mostrar un centro por id
    public function obtenerCentroPorID($id)
    {
        $centro = Centro::findOrFail($id);

        return response()->json($centro);
    }

    // Crear un centro
    public function agregarCentro(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());
        $datos['numero_total_aula'] ??= 0;
        $centro = Centro::create($datos);

        return response()->json($centro, 201);
    }

    // Actualizar un centro
    public function actualizarCentro(Request $request, $id)
    {
        $centro = Centro::find($id);

        if (! $centro) {
            return response()->json(['mensaje' => 'Centro no encontrado'], 404);
        }

        $datos = $request->validate($this->reglas(), $this->mensajes());
        $datos['numero_total_aula'] ??= $centro->numero_total_aula ?? 0;

        $centro->update($datos);

        return response()->json($centro);
    }

    // Eliminar un centro
    public function eliminarCentro($id)
    {
        $centro = Centro::find($id);

        if (! $centro) {
            return response()->json(['mensaje' => 'Centro no encontrado'], 404);
        }

        try {
            $centro->delete();
            return response()->json(['mensaje' => 'Centro eliminado']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: el centro tiene cursos asociados',
            ], 409);
        }
    }

    /* =========================================================
     *  API DE CONTACTOS DE CENTRO (JSON)
     * ========================================================= */

    public function listarContactos()
    {
        return response()->json(CentroContactos::with('centro')->get());
    }

    public function obtenerContactoPorID($id)
    {
        $contacto = CentroContactos::findOrFail($id);

        return response()->json($contacto);
    }

    public function agregarContacto(Request $request)
    {
        $datos = $request->validate($this->reglasContacto(), $this->mensajes());

        $contacto = CentroContactos::create($datos);

        return response()->json($contacto, 201);
    }

    public function actualizarContacto(Request $request, $id)
    {
        $contacto = CentroContactos::find($id);

        if (! $contacto) {
            return response()->json(['mensaje' => 'Contacto no encontrado'], 404);
        }

        $datos = $request->validate($this->reglasContacto(), $this->mensajes());

        $contacto->update($datos);

        return response()->json($contacto);
    }

    public function eliminarContacto($id)
    {
        $contacto = CentroContactos::find($id);

        if (! $contacto) {
            return response()->json(['mensaje' => 'Contacto no encontrado'], 404);
        }

        $contacto->delete();

        return response()->json(['mensaje' => 'Contacto eliminado']);
    }

    /* =========================================================
     *  AUXILIARES (compartidos por formulario y API)
     * ========================================================= */

    private function reglas(): array
    {
        return [
            // Los centros internos son propios; los externos pertenecen a una empresa cliente
            'empresa_id'        => 'nullable|required_if:tipo_centro,EXTERNO|integer|exists:empresa,id',
            'nombre'            => 'required|string|max:45',
            'tipo_centro'       => ['required', Rule::enum(TipoCentro::class)],
            'direccion'         => 'required|string|max:45',
            'municipio'         => 'required|string|max:45',
            'provincia'         => 'required|string|max:45',
            'codigo_postal'     => 'required|digits:5',
            'telefono'          => 'required|string|max:45',
            'email'             => 'required|email|max:45',   // NOT NULL en la BD
            'numero_total_aula' => 'nullable|integer|min:0',
        ];
    }

    private function reglasContacto(): array
    {
        return [
            'centro_id'         => 'required|integer|exists:centro,id',
            'curso_id'          => 'nullable|integer|exists:curso,id',
            'nombre'            => 'required|string|max:45',
            'contacto'          => ['required', Rule::enum(Contacto::class)],
            'telefono_personal' => 'required|string|max:45',   // NOT NULL en la BD
            'email_personal'    => 'required|email|max:45',    // NOT NULL en la BD
        ];
    }

    private function mensajes(): array
    {
        return [
            'required'              => 'Este campo es obligatorio.',
            'max'                   => 'Máximo :max caracteres.',
            'email'                 => 'El email no es válido.',
            'integer'               => 'Debe ser un número entero.',
            'enum'                  => 'El valor elegido no es válido.',
            'empresa_id.exists'     => 'La empresa elegida no existe.',
            'empresa_id.required_if' => 'Un centro externo debe pertenecer a una empresa.',
            'codigo_postal.digits'  => 'El código postal debe tener 5 números.',
            'min'                   => 'El valor mínimo es :min.',
            'centro_id.exists'      => 'El centro elegido no existe.',
            'curso_id.exists'       => 'El curso elegido no existe.',
        ];
    }

    // [id => nombre] de empresas y [valor => texto] de tipos de centro
    private function desplegables(): array
    {
        // Solo las empresas que ve su rol (coordinador: formadoras y clientes; administrador: todas)
        $empresas = Empresa::visiblesPara(Rol::principal(Auth::user()->persona_id))->orderBy('nombre')->pluck('nombre', 'id');

        $tiposCentro = collect(TipoCentro::cases())
            ->mapWithKeys(fn ($t) => [$t->value => ucfirst(strtolower($t->value))]);

        return compact('empresas', 'tiposCentro');
    }

    // Rol y nombre del usuario conectado (menú y barra superior)
    private function datosUsuario(): array
    {
        $personaId = Auth::user()->persona_id;

        $rol     = Rol::principal($personaId);
        $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

        $nombre = collect([$persona?->nombre, $persona?->primer_apellido, $persona?->segundo_apellido])
            ->filter()
            ->implode(' ');

        return compact('rol', 'nombre');
    }
}