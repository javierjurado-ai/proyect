<?php

namespace App\Http\Controllers;

use App\Models\Enums\Modalidad;
use App\Models\Formacion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormacionController extends Controller
{
    // Muestra el listado de formaciones (vista formacion/tabla.blade.php)
    public function index()
    {
        $formaciones = Formacion::orderBy('nombre')->paginate(15);

        return view('formacion.tabla', compact('formaciones'));
    }

    // Guarda lo enviado desde el formulario y vuelve a la tabla
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre'        => 'required|string|max:100',
            'codigo_accion' => 'required|integer|min:1',
            'modalidad'     => ['required', Rule::enum(Modalidad::class)],
            'sector'        => 'required|string|max:60',
            'horas_totales' => 'nullable|integer|min:1|max:9999',
            'activo'        => 'nullable|boolean',
        ]);

        Formacion::create($datos);

        return redirect()
            ->route('formacion.index')
            ->with('ok', 'Formación guardada correctamente.');
    }

    public function crearFormulario()
    {
        $modalidades = collect(Modalidad::cases())->mapWithKeys(
            fn($modalidad) => [$modalidad->value => ucfirst($modalidad->name)]
        );


        return view('formacion.formulario', compact('modalidades'));
    }

    // Listar todas las formaciones
    public function listarFormacion()
    {
        return response()->json(Formacion::all());
    }

    // Mostrar una formación concreta
    public function obtenerFormacionPorID($id)
    {
        $formacion = Formacion::find($id);

        if (! $formacion) {
            return response()->json(['mensaje' => 'Formación no encontrada'], 404);
        }

        return response()->json($formacion);
    }

    // Crear una nueva formación
    public function crearFormacion(Request $request)
    {
        $datos = $request->validate([
            'nombre'        => 'required|string|max:100',
            'codigo_accion' => 'required|integer|min:1',
            'modalidad'     => ['required', Rule::enum(Modalidad::class)],
            'sector'        => 'required|string|max:60',
            'horas_totales' => 'nullable|integer|min:1|max:9999',
            'activo'        => 'nullable|boolean',
        ]);

        $formacion = Formacion::create($datos);

        return response()->json($formacion, 201);
    }

    // Actualizar una formación
    public function actualizarFormacion(Request $request, $id)
    {
        $formacion = Formacion::find($id);

        if (! $formacion) {
            return response()->json(['mensaje' => 'Formación no encontrada'], 404);
        }

        $datos = $request->validate([
            'nombre'        => 'required|string|max:100',
            'codigo_accion' => 'required|integer|min:1',
            'modalidad'     => ['required', Rule::enum(Modalidad::class)],
            'sector'        => 'required|string|max:60',
            'horas_totales' => 'nullable|integer|min:1|max:9999',
            'activo'        => 'nullable|boolean',
        ]);

        $formacion->update($datos);

        return response()->json($formacion);
    }

    // Eliminar una formación
    public function eliminarFormacion($id)
    {
        $formacion = Formacion::find($id);

        if (! $formacion) {
            return response()->json(['mensaje' => 'Formación no encontrada'], 404);
        }

        try {
            $formacion->delete();
            return response()->json(['mensaje' => 'Formación eliminada']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: la formación tiene cursos asociados',
            ], 409);
        }
    }
}