<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Enums\EstadoSerie;
use App\Models\Enums\LOPD;
use App\Models\Enums\TipoArticulo;
use App\Models\Enums\UnidadArticulo;
use App\Models\Stock;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArticuloController extends Controller
{
    // Listar artículos con su stock total (?tipo=EQUIPO, ?activo=1, ?buscar=texto)
    public function listarArticulos(Request $request)
    {
        $articulos = Articulo::with('proveedorHabitual')
            ->withSum('stock as stock_total', 'cantidad')
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->when($request->filled('buscar'), fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', '%' . $request->buscar . '%')
                ->orWhere('codigo', 'like', '%' . $request->buscar . '%')))
            ->orderBy('nombre')
            ->get();

        return response()->json($articulos);
    }

    // Obtener un artículo con su stock por almacén
    public function obtenerArticuloPorID($id)
    {
        $articulo = Articulo::with(['proveedorHabitual', 'stock.almacen'])->find($id);

        if (! $articulo) {
            return response()->json(['mensaje' => 'Artículo no encontrado'], 404);
        }

        return response()->json($articulo);
    }

    // Crear un artículo (el stock entra después con un movimiento de entrada)
    public function agregarArticulo(Request $request)
    {
        $datos = $request->validate($this->reglas());

        $articulo = Articulo::create($datos);

        return response()->json($articulo->fresh(), 201);
    }

    // Actualizar un artículo
    public function actualizarArticulo(Request $request, $id)
    {
        $articulo = Articulo::find($id);

        if (! $articulo) {
            return response()->json(['mensaje' => 'Artículo no encontrado'], 404);
        }

        $datos = $request->validate($this->reglas($articulo->id));

        // No se puede cambiar el control por serie si ya hay stock o unidades
        $cambiaSerie = isset($datos['controla_serie']) && $datos['controla_serie'] !== $articulo->controla_serie->value;
        if ($cambiaSerie && ($articulo->unidades()->exists() || $articulo->stock()->where('cantidad', '>', 0)->exists())) {
            return response()->json([
                'mensaje' => 'No se puede cambiar "controla_serie": el artículo ya tiene stock o unidades',
            ], 409);
        }

        $articulo->update($datos);

        return response()->json($articulo);
    }

    // Eliminar un artículo (solo si nunca se ha movido; si no, desactivarlo)
    public function eliminarArticulo($id)
    {
        $articulo = Articulo::find($id);

        if (! $articulo) {
            return response()->json(['mensaje' => 'Artículo no encontrado'], 404);
        }

        if ($articulo->stock()->where('cantidad', '>', 0)->exists()) {
            return response()->json(['mensaje' => 'No se puede eliminar: el artículo tiene stock'], 409);
        }

        try {
            Stock::where('articulo_id', $articulo->id)->delete(); // filas a 0
            $articulo->delete();
            return response()->json(['mensaje' => 'Artículo eliminado']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: el artículo tiene movimientos, unidades o peticiones. Puedes desactivarlo.',
            ], 409);
        }
    }

    // Artículos activos cuyo stock total está por debajo del mínimo
    public function articulosBajoMinimo()
    {
        $articulos = Articulo::where('activo', true)
            ->where('stock_minimo', '>', 0)
            ->withSum('stock as stock_total', 'cantidad')
            ->get()
            ->filter(fn ($a) => (int) $a->stock_total < $a->stock_minimo)
            ->values();

        return response()->json($articulos);
    }

    // Unidades con nº de serie de un artículo (?estado=DISPONIBLE)
    public function listarUnidades(Request $request, $id)
    {
        $articulo = Articulo::find($id);

        if (! $articulo) {
            return response()->json(['mensaje' => 'Artículo no encontrado'], 404);
        }

        $unidades = $articulo->unidades()
            ->with('almacen', 'curso')
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->orderBy('numero_serie')
            ->get();

        return response()->json($unidades);
    }

    // Enviar una unidad a reparación o devolverla a disponible.
    // Las altas, bajas y salidas de unidades se hacen con movimientos de almacén.
    public function cambiarEstadoUnidad(Request $request, $id, $unidadId)
    {
        $unidad = ArticuloSerie::where('articulo_id', $id)->find($unidadId);

        if (! $unidad) {
            return response()->json(['mensaje' => 'Unidad no encontrada'], 404);
        }

        $datos = $request->validate([
            'estado' => ['required', Rule::in([EstadoSerie::Disponible->value, EstadoSerie::EnReparacion->value])],
        ]);

        if (! in_array($unidad->estado, [EstadoSerie::Disponible, EstadoSerie::EnReparacion], true)) {
            return response()->json([
                'mensaje' => 'Solo se puede cambiar el estado de una unidad que está en el almacén',
            ], 409);
        }

        $unidad->update($datos);

        return response()->json($unidad);
    }

    private function reglas(?int $id = null): array
    {
        return [
            'codigo'                => ['required', 'string', 'max:20', Rule::unique('articulo', 'codigo')->ignore($id)],
            'nombre'                => 'required|string|max:100',
            'descripcion'           => 'nullable|string|max:300',
            'tipo'                  => ['required', Rule::enum(TipoArticulo::class)],
            'unidad'                => ['sometimes', Rule::enum(UnidadArticulo::class)],
            'retornable'            => ['sometimes', Rule::enum(LOPD::class)],
            'controla_serie'        => ['sometimes', Rule::enum(LOPD::class)],
            'precio_coste'          => 'sometimes|numeric|min:0',
            'precio_venta'          => 'nullable|numeric|min:0',
            'tipo_iva'              => 'sometimes|numeric|min:0|max:100',
            'stock_minimo'          => 'sometimes|integer|min:0',
            'proveedor_habitual_id' => 'nullable|integer|exists:empresa,id',
            'activo'                => 'sometimes|boolean',
        ];
    }
}
