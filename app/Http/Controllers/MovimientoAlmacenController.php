<?php

namespace App\Http\Controllers;

use App\Models\Enums\TipoMovimiento;
use App\Models\MovimientoAlmacen;
use App\Services\AlmacenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class MovimientoAlmacenController extends Controller
{
    public function __construct(private AlmacenService $almacenService)
    {
    }

    // Listar movimientos (?tipo=, ?almacen_id=, ?articulo_id=, ?curso_id=, ?desde=, ?hasta=)
    public function listarMovimientos(Request $request)
    {
        $movimientos = MovimientoAlmacen::with(['almacenOrigen', 'almacenDestino', 'persona', 'lineas.articulo'])
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('almacen_id'), fn ($q) => $q->where(fn ($q) => $q
                ->where('almacen_origen_id', $request->almacen_id)
                ->orWhere('almacen_destino_id', $request->almacen_id)))
            ->when($request->filled('articulo_id'), fn ($q) => $q->whereHas('lineas', fn ($l) => $l->where('articulo_id', $request->articulo_id)))
            ->when($request->filled('curso_id'), fn ($q) => $q->where('curso_id', $request->curso_id))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->hasta))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json($movimientos);
    }

    // Obtener un movimiento con sus líneas
    public function obtenerMovimientoPorID($id)
    {
        $movimiento = MovimientoAlmacen::with([
            'almacenOrigen', 'almacenDestino', 'curso', 'peticionLogistica',
            'proveedor', 'facturaProveedora', 'persona', 'lineas.articulo', 'lineas.unidad',
        ])->find($id);

        if (! $movimiento) {
            return response()->json(['mensaje' => 'Movimiento no encontrado'], 404);
        }

        return response()->json($movimiento);
    }

    // Registrar una entrada de compra, traspaso, ajuste o baja.
    // Lineas: [{articulo_id, cantidad, coste_unitario?, articulo_serie_id? | numero_serie?}]
    public function registrarMovimiento(Request $request)
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in([
                TipoMovimiento::EntradaCompra->value,
                TipoMovimiento::Traspaso->value,
                TipoMovimiento::Ajuste->value,
                TipoMovimiento::Baja->value,
            ])],
            'almacen_origen_id'             => 'nullable|integer|exists:almacen,id',
            'almacen_destino_id'            => 'nullable|integer|exists:almacen,id',
            'proveedor_id'                  => 'nullable|integer|exists:empresa,id',
            'factura_empresa_proveedora_id' => 'nullable|integer|exists:factura_empresa_proveedora,id',
            'referencia'                    => 'nullable|string|max:45',
            'observaciones'                 => 'nullable|string|max:200',
            'lineas'                        => 'required|array|min:1',
            'lineas.*.articulo_id'          => 'required|integer|exists:articulo,id',
            'lineas.*.cantidad'             => 'required|integer|min:1',
            'lineas.*.coste_unitario'       => 'nullable|numeric|min:0',
            'lineas.*.articulo_serie_id'    => 'nullable|integer|exists:articulo_serie,id',
            'lineas.*.numero_serie'         => 'nullable|string|max:60',
        ], [
            'tipo.in' => 'Las salidas y devoluciones de cursos se registran desde la petición de logística.',
        ]);

        $lineas = $datos['lineas'];
        unset($datos['lineas']);
        $datos['persona_id'] = Auth::user()->persona_id;

        $movimiento = $this->almacenService->registrarMovimiento($datos, $lineas);

        return response()->json($movimiento, 201);
    }
}
