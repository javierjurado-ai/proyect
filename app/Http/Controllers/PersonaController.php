<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Persona;
use App\Models\enums\Genero;
use App\Models\enums\LOPD;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonaController extends Controller
{
    /* =========================================================
     *  FORMULARIO (web)
     * ========================================================= */

    // Muestra el formulario de alta
    public function create()
    {
        // [valor => texto] para el desplegable de género, sacado del enum
        $generos = collect(Genero::cases())
            ->mapWithKeys(fn ($g) => [$g->value => ucfirst(strtolower($g->value))]);

        // [id => nombre] de la tabla empresa para el desplegable
        $empresas = Empresa::orderBy('nombre')->pluck('nombre', 'id');

        return view('persona.formulario', compact('generos', 'empresas'));
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

    /* =========================================================
     *  API (JSON)
     * ========================================================= */

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

        $persona = Persona::create($datos);

        return response()->json($persona, 201);
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
            'empresa_id'              => 'required|exists:empresa,id',
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