<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\enums\Genero;
use App\Models\enums\LOPD;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonaFormularioController extends Controller
{
    public function create()
    {
        $generos = collect(Genero::cases())
            ->mapWithKeys(fn ($g) => [$g->value => ucfirst(strtolower($g->value))]);

        return view('persona.formulario', compact('generos'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'DNI' => $request->filled('DNI') ? strtoupper(trim($request->DNI)) : null,
            'NIE' => $request->filled('NIE') ? strtoupper(trim($request->NIE)) : null,
        ]);

        $datos = $request->validate([
            'nombre'                  => 'required|string|max:45',
            'primer_apellido'         => 'required|string|max:45',
            'segundo_apellido'        => 'required|string|max:45',
            'fecha_nacimiento'        => 'required|date|before_or_equal:today',
            'genero'                  => ['required', Rule::enum(Genero::class)],
            'nacionalidad'            => 'required|string|max:45',
            'telefono'                => 'required|string|max:20',
            'direccion'               => 'required|string|max:150',
            'email'                   => 'required|email|max:100',
            'DNI'                     => ['nullable', 'required_without:NIE', 'prohibits:NIE', 'max:15', 'regex:/^\d{8}[A-Z]$/'],
            'NIE'                     => ['nullable', 'required_without:DNI', 'max:15', 'regex:/^[XYZ]\d{7}[A-Z]$/'],
            'numero_seguridad_social' => 'nullable|string|max:20',
            'LOPD'                    => ['required', Rule::enum(LOPD::class)],
        ], [
            'required'                         => 'Este campo es obligatorio.',
            'max'                              => 'Máximo :max caracteres.',
            'email'                            => 'El email no es válido.',
            'date'                             => 'La fecha no es válida.',
            'enum'                             => 'El valor elegido no es válido.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'DNI.required_without'             => 'Debes indicar el DNI o el NIE.',
            'NIE.required_without'             => 'Debes indicar el DNI o el NIE.',
            'DNI.prohibits'                    => 'Indica solo el DNI o el NIE, no los dos.',
            'DNI.regex'                        => 'El DNI debe tener 8 números y una letra.',
            'NIE.regex'                        => 'El NIE debe empezar por X, Y o Z, seguido de 7 números y una letra.',
        ]);

        Persona::create($datos);

        return redirect()
            ->route('persona.create')
            ->with('ok', 'Persona guardada correctamente.');
    }
}