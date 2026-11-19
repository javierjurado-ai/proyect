<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Presupuesto;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresupuestoController extends Controller
{
    /* =========================================================
     *  FORMULARIO (web)
     * ========================================================= */

    // Muestra el listado de presupuestos (vista presupuesto/index.blade.php)
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

        $presupuestos = Presupuesto::orderByDesc('fecha_bonificado')->paginate(15);

        return view('presupuesto.index', compact('presupuestos', 'rol', 'nombre'));
    }

    // Muestra el formulario de alta
    public function create()
    {
        return view('presupuesto.formulario');
    }

    // Guarda lo enviado desde el formulario y vuelve a la tabla
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        Presupuesto::create($datos);

        return redirect()
            ->route('presupuesto.index')
            ->with('ok', 'Presupuesto guardado correctamente.');
    }

    /* =========================================================
     *  API (JSON)
     * ========================================================= */

    public function listarPresupuesto()
    {
        return response()->json(Presupuesto::all());
    }

    public function obtenerPresupuestoPorID($id)
    {
        $presupuesto = Presupuesto::findOrFail($id);

        return response()->json($presupuesto, 200);
    }

    public function agregarPresupuesto(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        return response()->json(Presupuesto::create($datos), 201);
    }

    public function actualizarPresupuesto($id, Request $request)
    {
        $presupuesto = Presupuesto::findOrFail($id);

        $datos = $request->validate($this->reglas(), $this->mensajes());

        $presupuesto->update($datos);

        return response()->json($presupuesto, 200);
    }

    public function eliminarPresupuesto($id)
    {
        $presupuesto = Presupuesto::findOrFail($id);

        $presupuesto->delete();

        return response()->json(['mensaje' => 'presupuesto eliminado'], 200);
    }

    /* =========================================================
     *  AUXILIARES (compartidos por formulario y API)
     * ========================================================= */

    private function reglas(): array
    {
        return [
            'numero_alumnos'      => ['required', 'integer', 'min:1'],
            'nombre_formacion'    => ['required', 'string', 'max:45'],
            'horas_totales'       => ['required', 'integer', 'min:1'],
            'ubicacion_formacion' => ['required', 'string', 'max:90'],
            'objetivos'           => ['required', 'string', 'max:150'],
            'contenidos'          => ['required', 'string', 'max:450'],
            'importe_bonificado'  => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'fecha_bonificado'    => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'required'                        => 'Este campo es obligatorio.',
            'string'                          => 'Debe ser un texto.',
            'integer'                         => 'Debe ser un número entero.',
            'numeric'                         => 'Debe ser un número.',
            'date'                            => 'La fecha no es válida.',
            // min/max cambian según sea texto o número
            'min'                             => ['numeric' => 'El valor mínimo es :min.', 'string' => 'Mínimo :min caracteres.'],
            'max'                             => ['numeric' => 'El valor máximo es :max.', 'string' => 'Máximo :max caracteres.'],
            'numero_alumnos.min'              => 'Debe haber al menos :min alumno.',
            'horas_totales.min'               => 'Debe tener al menos :min hora.',
            'importe_bonificado.decimal'      => 'El importe puede tener como máximo 2 decimales.',
            'fecha_bonificado.after_or_equal' => 'La fecha de bonificación no puede ser anterior a hoy.',
        ];
    }
}