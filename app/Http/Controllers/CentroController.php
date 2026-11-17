<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use App\Models\Empresa;
use App\Models\Enums\TipoCentro;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CentroController extends Controller
{
    /* =========================================================
     *  FORMULARIO Y TABLA (web)
     * ========================================================= */

    // Muestra el listado de centros (vista centro/tabla.blade.php)
    public function index()
    {
        $centros = Centro::with('empresa')->orderBy('nombre')->paginate(15);

        return view('centro.tabla', compact('centros'));
    }

    // Muestra el formulario de alta
    public function crearFormulario()
    {
        $empresas = Empresa::orderBy('nombre')->pluck('nombre', 'id');

        // [valor => texto] para el desplegable, sacado del enum
        $tiposCentro = collect(TipoCentro::cases())
            ->mapWithKeys(fn ($t) => [$t->value => ucfirst(strtolower($t->value))]);

        return view('centro.formulario', compact('empresas', 'tiposCentro'));
    }

    // Guarda lo enviado desde el formulario y vuelve a la tabla
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        Centro::create($datos);

        return redirect()
            ->route('centro.index')
            ->with('ok', 'Centro guardado correctamente.');
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
     *  AUXILIARES (compartidos por formulario y API)
     * ========================================================= */

    private function reglas(): array
    {
        return [
            'empresa_id'        => 'required|integer|exists:empresa,id',
            'nombre'            => 'required|string|max:45',
            'tipo_centro'       => ['required', Rule::enum(TipoCentro::class)],
            'direccion'         => 'required|string|max:45',
            'municipio'         => 'required|string|max:45',
            'provincia'         => 'required|string|max:45',
            'codigo_postal'     => 'required|digits:5',
            'telefono'          => 'required|string|max:45',
            'email'             => 'nullable|email|max:45',
            'numero_total_aula' => 'nullable|integer|min:0',
        ];
    }

    private function mensajes(): array
    {
        return [
            'required'             => 'Este campo es obligatorio.',
            'max'                  => 'Máximo :max caracteres.',
            'email'                => 'El email no es válido.',
            'integer'              => 'Debe ser un número entero.',
            'enum'                 => 'El valor elegido no es válido.',
            'empresa_id.exists'    => 'La empresa elegida no existe.',
            'codigo_postal.digits' => 'El código postal debe tener 5 números.',
            'numero_total_aula.min' => 'No puede ser negativo.',
        ];
    }
}