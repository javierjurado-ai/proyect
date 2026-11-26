<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\MovimientoAlmacen;
use App\Models\Stock;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    use Concerns\DatosUsuario;

    // Vista web: stock del almacén elegido (?almacen_id=), material prestado y últimos movimientos
    public function index(Request $request)
    {
        [$rol, $nombre] = $this->rolYNombre();

        $almacenes = Almacen::where('activo', true)->orderBy('nombre')->get();
        $almacen = $almacenes->firstWhere('id', (int) $request->query('almacen_id'))
            ?? $this->misAlmacenes()->first();

        $stockAqui = $almacen ? Stock::where('almacen_id', $almacen->id)->pluck('cantidad', 'articulo_id') : collect();

        // Unidades prestadas a cursos por artículo (las que tienen nº de serie)
        $prestadas = ArticuloSerie::where('estado', 'PRESTADO')->selectRaw('articulo_id, COUNT(*) as n')
            ->groupBy('articulo_id')->pluck('n', 'articulo_id');

        $articulos = Articulo::where('activo', true)->withSum('stock as stock_total', 'cantidad')->orderBy('nombre')->get()
            ->map(fn ($a) => [
                'articulo'   => $a,
                'aqui'       => (int) ($stockAqui[$a->id] ?? 0),
                'total'      => (int) $a->stock_total,
                'prestados'  => (int) ($prestadas[$a->id] ?? 0),
                'bajoMinimo' => $a->stock_minimo > 0 && (int) $a->stock_total < $a->stock_minimo,
            ]);

        // Equipos prestados agrupados por curso
        $prestadosPorCurso = ArticuloSerie::with(['articulo', 'curso.formacion'])
            ->where('estado', 'PRESTADO')->orderBy('numero_serie')->get()->groupBy('curso_id');

        $movimientos = $almacen
            ? MovimientoAlmacen::with(['lineas.articulo', 'curso.formacion', 'almacenOrigen', 'almacenDestino', 'persona'])
                ->where(fn ($q) => $q->where('almacen_origen_id', $almacen->id)->orWhere('almacen_destino_id', $almacen->id))
                ->orderByDesc('fecha')->orderByDesc('id')->limit(10)->get()
            : collect();

        $resumen = [
            'articulos'   => $articulos->count(),
            'disponibles' => $almacen ? ArticuloSerie::where('almacen_id', $almacen->id)->where('estado', 'DISPONIBLE')->count() : 0,
            'prestados'   => $prestadas->sum(),
            'bajoMinimo'  => $articulos->where('bajoMinimo', true)->count(),
        ];

        return view('almacen.index', compact('rol', 'nombre', 'almacenes', 'almacen', 'articulos', 'prestadosPorCurso', 'movimientos', 'resumen'));
    }

    // Listar todos los almacenes (?activo=1 para solo los activos)
    public function listarAlmacenes(Request $request)
    {
        $almacenes = Almacen::with('centro', 'responsable')
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('nombre')
            ->get();

        return response()->json($almacenes);
    }

    // Obtener un almacén por id
    public function obtenerAlmacenPorID($id)
    {
        $almacen = Almacen::with('centro', 'responsable')->find($id);

        if (! $almacen) {
            return response()->json(['mensaje' => 'Almacén no encontrado'], 404);
        }

        return response()->json($almacen);
    }

    // Crear un almacén
    public function agregarAlmacen(Request $request)
    {
        $datos = $request->validate($this->reglas());

        $almacen = Almacen::create($datos);

        return response()->json($almacen->fresh(), 201);
    }

    // Actualizar un almacén
    public function actualizarAlmacen(Request $request, $id)
    {
        $almacen = Almacen::find($id);

        if (! $almacen) {
            return response()->json(['mensaje' => 'Almacén no encontrado'], 404);
        }

        $datos = $request->validate($this->reglas());

        $almacen->update($datos);

        return response()->json($almacen);
    }

    // Eliminar un almacén (solo si no tiene stock, movimientos ni peticiones)
    public function eliminarAlmacen($id)
    {
        $almacen = Almacen::find($id);

        if (! $almacen) {
            return response()->json(['mensaje' => 'Almacén no encontrado'], 404);
        }

        if ($almacen->stock()->where('cantidad', '>', 0)->exists()) {
            return response()->json(['mensaje' => 'No se puede eliminar: el almacén tiene stock'], 409);
        }

        try {
            Stock::where('almacen_id', $almacen->id)->delete(); // filas a 0
            $almacen->delete();
            return response()->json(['mensaje' => 'Almacén eliminado']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: el almacén tiene movimientos o peticiones. Puedes desactivarlo.',
            ], 409);
        }
    }

    // Stock del almacén: cada artículo con su cantidad y si está bajo mínimo
    public function stockAlmacen($id)
    {
        $almacen = Almacen::find($id);

        if (! $almacen) {
            return response()->json(['mensaje' => 'Almacén no encontrado'], 404);
        }

        $stock = Stock::with('articulo')
            ->where('almacen_id', $almacen->id)
            ->get()
            ->map(fn (Stock $s) => [
                'articulo_id'  => $s->articulo_id,
                'codigo'       => $s->articulo?->codigo,
                'nombre'       => $s->articulo?->nombre,
                'cantidad'     => $s->cantidad,
                'stock_minimo' => $s->articulo?->stock_minimo,
                'bajo_minimo'  => $s->articulo && $s->cantidad < $s->articulo->stock_minimo,
            ])
            ->sortBy('nombre')
            ->values();

        return response()->json(['almacen' => $almacen, 'stock' => $stock]);
    }

    private function reglas(): array
    {
        return [
            'nombre'         => 'required|string|max:60',
            'direccion'      => 'required|string|max:150',
            'municipio'      => 'required|string|max:45',
            'provincia'      => 'required|string|max:45',
            'centro_id'      => 'nullable|integer|exists:centro,id',
            'responsable_id' => 'nullable|integer|exists:persona,id',
            'activo'         => 'sometimes|boolean',
        ];
    }
}
