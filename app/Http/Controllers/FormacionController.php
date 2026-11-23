<?php

namespace App\Http\Controllers;

use App\Models\Formacion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Support\Facades\Auth;

class FormacionController extends Controller
{
    /* =========================================================
     *  FORMULARIO Y TABLA (web)
     * ========================================================= */

    // Muestra el listado de formaciones (vista formacion/tabla.blade.php)
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
        $formaciones = Formacion::withCount('cursos')->orderBy('nombre')->paginate(15);

        return view('formacion.index', compact('formaciones', 'rol', 'nombre'));
    }

    // Guarda lo enviado desde el formulario y vuelve a la tabla
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas());

        // Asegura que si no viene marcado se guarde como falso (0) o verdadero (1)
        $datos['activo'] = $request->has('activo') ? 1 : 0;

        Formacion::create($datos);

        return redirect()
            ->route('formacion.index')
            ->with('ok', 'Formación guardada correctamente.');
    }

    public function crearFormulario()
    {
        $personaId = Auth::user()->persona_id;

        $rol     = Rol::where('persona_id', $personaId)->value('rol')?->value;
        $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

        $nombre = collect([$persona->nombre, $persona->primer_apellido, $persona->segundo_apellido])
            ->filter()
            ->implode(' ');

        return view('formacion.formulario', compact('rol', 'nombre'));
    }

    // Muestra el formulario de edición con los datos de la formación
    public function editarFormacionFormulario(Request $request, $id)
    {
        $personaId = Auth::user()->persona_id;

        $rol     = Rol::where('persona_id', $personaId)->value('rol')?->value;
        $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

        $nombre = collect([$persona->nombre, $persona->primer_apellido, $persona->segundo_apellido])
            ->filter()
            ->implode(' ');

        $formacion = Formacion::findOrFail($id);

        return view('formacion.formulario', compact('formacion', 'rol', 'nombre'));
    }

    // Actualiza la formación enviada desde el formulario web
    public function actualizarFormacionWeb(Request $request, $id)
    {
        $formacion = Formacion::findOrFail($id);

        $datos = $request->validate($this->reglas());

        // Asegura que si no viene marcado se guarde como falso (0) o verdadero (1)
        $datos['activo'] = $request->has('activo') ? 1 : 0;

        $formacion->update($datos);

        return redirect()
            ->route('formacion.index')
            ->with('ok', 'Formación actualizada correctamente.');
    }

    // Elimina la formación desde la tabla web
    public function eliminarFormacionWeb($id)
    {
        $formacion = Formacion::findOrFail($id);

        try {
            $formacion->delete();
            return redirect()
                ->route('formacion.index')
                ->with('ok', 'Formación eliminada correctamente.');
        } catch (QueryException $e) {
            return redirect()
                ->route('formacion.index')
                ->with('error', 'No se puede eliminar: la formación tiene cursos asociados.');
        }
    }

    /* =========================================================
     *  API REST
     * ========================================================= */

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
        $datos = $request->validate($this->reglas());

        // Si no se envía 'activo', la BD pone 1 por defecto
        $formacion = Formacion::create(array_filter($datos, fn ($v) => $v !== null));

        return response()->json($formacion, 201);
    }

    // Actualizar una formación
    public function actualizarFormacion(Request $request, $id)
    {
        $formacion = Formacion::find($id);

        if (! $formacion) {
            return response()->json(['mensaje' => 'Formación no encontrada'], 404);
        }

        $datos = $request->validate($this->reglas());

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

    /* =========================================================
     *  AUXILIARES
     * ========================================================= */

    // Columnas de la tabla formacion (ya no tiene 'modalidad' ni 'sector')
    private function reglas(): array
    {
        return [
            'nombre'        => 'required|string|max:100',
            'codigo_accion' => 'nullable|integer|min:1',   // int NULL en la BD
            'horas_totales' => 'nullable|numeric|min:1|max:9999',
            'activo'        => 'nullable|boolean',
        ];
    }
}