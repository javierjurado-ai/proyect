<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Articulo;
use App\Models\Curso;
use App\Models\Empresa;
use App\Models\Enums\EstadoFacturaProveedora;
use App\Models\Enums\FormaPago;
use App\Models\Enums\LOPD;
use App\Models\Enums\RolEmpresa;
use App\Models\Enums\TipoCoste;
use App\Models\Enums\TipoMovimiento;
use App\Models\FacturaEmpresaProveedora;
use App\Models\Rol;
use App\Services\AlmacenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Facturas RECIBIDAS de empresas proveedoras / formadoras o de docentes autónomos.
// Ciclo: PENDIENTE → PAGADA, o ANULADA si se registró por error.
// Un LOGISTA solo ve y gestiona las facturas que ha registrado él (persona_id).
class FacturaEmpresaProveedoraController extends Controller
{
    use Concerns\DatosUsuario;

    public function __construct(private AlmacenService $almacenService)
    {
    }

    /* =========================================================
     *  VISTAS WEB: COMPRAS
     * ========================================================= */

    // Listado de compras (?estado=pendientes|pagadas|anuladas). Un LOGISTA solo ve las suyas.
    public function index(Request $request)
    {
        [$rol, $nombre] = $this->rolYNombre();
        $filtro = $request->query('estado', 'todas');

        $todas = $this->consulta()->with(['empresa', 'docente', 'lineas'])->orderByDesc('fecha')->orderByDesc('id')->get();
        $sinPagar = $todas->where('estado', EstadoFacturaProveedora::Pendiente);

        $compras = match ($filtro) {
            'pendientes' => $sinPagar,
            'pagadas'    => $todas->where('estado', EstadoFacturaProveedora::Pagada),
            'anuladas'   => $todas->where('estado', EstadoFacturaProveedora::Anulada),
            default      => $todas,
        };

        $resumen = [
            'sinPagar'      => $sinPagar->count(),
            'vencenSemana'  => $sinPagar->filter(fn ($f) => $f->fecha_vencimiento && $f->fecha_vencimiento->lte(today()->addDays(7)))->count(),
            'pendiente'     => (float) $sinPagar->sum('total'),
            'esteMes'       => $todas->filter(fn ($f) => $f->fecha->isSameMonth(today()))->count(),
        ];
        $pestanas = [
            'todas'      => ['Todas', $todas->count()],
            'pendientes' => ['Sin pagar', $sinPagar->count()],
            'pagadas'    => ['Pagadas', $todas->where('estado', EstadoFacturaProveedora::Pagada)->count()],
            'anuladas'   => ['Anuladas', $todas->where('estado', EstadoFacturaProveedora::Anulada)->count()],
        ];

        return view('compras.index', compact('rol', 'nombre', 'compras', 'filtro', 'resumen', 'pestanas'));
    }

    // Formulario para registrar una compra (factura de proveedor con entrada en almacén)
    public function crearFormulario(Request $request)
    {
        [$rol, $nombre] = $this->rolYNombre();

        $proveedores = Empresa::where('activo', true)
            ->where(fn ($q) => $q->conRol(RolEmpresa::Proveedor)->orWhere(fn ($q) => $q->conRol(RolEmpresa::Formadora)))
            ->orderBy('nombre')->get();
        $almacenes = Almacen::where('activo', true)->orderBy('nombre')->get();
        $almacenId = (int) $request->query('almacen_id', $this->misAlmacenes()->first()?->id);
        $articulos = Articulo::where('activo', true)->orderBy('nombre')->get();
        $cursos = Curso::with('formacion')->whereDate('fecha_fin', '>=', today()->subMonths(3))->orderBy('fecha_inicio')->get();

        return view('compras.formulario', compact('rol', 'nombre', 'proveedores', 'almacenes', 'almacenId', 'articulos', 'cursos'));
    }

    // Detalle de una compra: datos, líneas, totales y entradas de almacén que generó.
    // Un LOGISTA solo puede abrir las suyas (consulta() ya lo filtra; si no es suya, 404).
    public function vistaCompra($id)
    {
        [$rol, $nombre] = $this->rolYNombre();

        $compra = $this->consulta()->with([
            'empresa', 'docente', 'curso.formacion', 'persona', 'lineas.articulo', 'lineas.curso.formacion',
            'movimientos.almacenDestino', 'movimientos.persona', 'movimientos.lineas.articulo', 'movimientos.lineas.unidad',
        ])->findOrFail($id);

        return view('compras.visualizar', compact('rol', 'nombre', 'compra'));
    }

    // Listar (?estado=, ?empresa_id=, ?docente_id=, ?curso_id=, ?vencidas=1)
    public function listarFacturasProveedora(Request $request)
    {
        $facturas = $this->consulta()->with(['empresa', 'docente', 'curso.formacion'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('empresa_id'), fn ($q) => $q->where('empresa_id', $request->empresa_id))
            ->when($request->filled('docente_id'), fn ($q) => $q->where('docente_id', $request->docente_id))
            ->when($request->filled('curso_id'), fn ($q) => $q->where('curso_id', $request->curso_id))
            ->when($request->boolean('vencidas'), fn ($q) => $q
                ->where('estado', EstadoFacturaProveedora::Pendiente->value)
                ->whereDate('fecha_vencimiento', '<', today()))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get()
            ->each(fn ($f) => $f->append('nombre_emisor'));

        return response()->json($facturas);
    }

    // Obtener una factura con sus líneas y las entradas de almacén que generó
    public function obtenerFacturaProveedoraPorID($id)
    {
        $factura = $this->consulta()->with(['empresa', 'docente', 'curso.formacion', 'lineas.articulo', 'movimientos'])
            ->find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }

        return response()->json($factura->append('nombre_emisor'));
    }

    // Registrar una factura recibida con sus líneas.
    // Con generar_entrada = true y almacen_id, las líneas con artículo crean una ENTRADA_COMPRA.
    // Para artículos con nº de serie, cada unidad se indica en lineas.*.numeros_serie[].
    public function agregarFacturaProveedora(Request $request)
    {
        $this->prepararFormulario($request);

        $datos = $request->validate($this->reglas() + [
            'generar_entrada'          => 'sometimes|boolean',
            'almacen_id'               => 'required_if:generar_entrada,true,1|nullable|integer|exists:almacen,id',
            'lineas.*.numeros_serie'   => 'nullable|array',
            'lineas.*.numeros_serie.*' => 'string|max:60',
        ]);
        $datos['retencion_irpf'] ??= 0; // el campo vacío del formulario llega como null

        if ($error = $this->comprobarEmisor($datos, $request)) {
            return $request->expectsJson() ? $error : back()->withInput()->withErrors(['empresa_id' => $error->getData()->mensaje]);
        }

        $factura = DB::transaction(function () use ($datos, $request) {
            $lineas = $datos['lineas'];
            $generarEntrada = $request->boolean('generar_entrada');
            $almacenId = $datos['almacen_id'] ?? null;
            unset($datos['lineas'], $datos['generar_entrada'], $datos['almacen_id']);

            $factura = FacturaEmpresaProveedora::create($datos + [
                'fecha_registro' => today(),
                'estado'         => EstadoFacturaProveedora::Pendiente,
                'persona_id'     => Auth::user()->persona_id,
            ]);
            $this->guardarLineas($factura, $lineas);

            if ($generarEntrada) {
                $this->generarEntrada($factura, $lineas, $almacenId);
            }

            return $factura;
        });

        if (! $request->expectsJson()) {
            return redirect()->route('compra.index')->with('exito', 'Compra ' . $factura->numero_factura . ' registrada'
                . ($factura->movimientos()->exists() ? ' y material dado de entrada en el almacén.' : '.'));
        }

        return response()->json($factura->load('lineas', 'movimientos'), 201);
    }

    // Actualizar una factura PENDIENTE (sustituye sus líneas).
    // Si ya generó entrada de almacén, no se pueden cambiar las líneas.
    public function actualizarFacturaProveedora(Request $request, $id)
    {
        $factura = $this->consulta()->find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        if ($factura->estado !== EstadoFacturaProveedora::Pendiente) {
            return response()->json(['mensaje' => 'Solo se puede modificar una factura pendiente'], 409);
        }
        if ($factura->movimientos()->exists()) {
            return response()->json(['mensaje' => 'La factura ya generó una entrada de almacén: corrige el stock con un ajuste'], 409);
        }

        $datos = $request->validate($this->reglas($factura->id));
        if (array_key_exists('retencion_irpf', $datos)) {
            $datos['retencion_irpf'] ??= 0;
        }

        if ($error = $this->comprobarEmisor($datos, $request)) {
            return $error;
        }

        DB::transaction(function () use ($factura, $datos) {
            $lineas = $datos['lineas'];
            unset($datos['lineas']);

            $factura->update($datos);
            $factura->lineas()->delete();
            $this->guardarLineas($factura, $lineas);
        });

        return response()->json($factura->fresh('lineas'));
    }

    // Marcar como pagada
    public function marcarPagada(Request $request, $id)
    {
        $factura = $this->consulta()->find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        if ($factura->estado !== EstadoFacturaProveedora::Pendiente) {
            return $request->expectsJson()
                ? response()->json(['mensaje' => 'La factura no está pendiente de pago'], 409)
                : back()->withErrors(['compra' => 'La factura no está pendiente de pago']);
        }

        $datos = $request->validate([
            'fecha_pago' => 'required|date',
            'forma_pago' => ['sometimes', Rule::enum(FormaPago::class)],
        ]);

        $factura->update($datos + ['estado' => EstadoFacturaProveedora::Pagada]);

        if (! $request->expectsJson()) {
            return back()->with('exito', 'Compra ' . $factura->numero_factura . ' marcada como pagada.');
        }

        return response()->json($factura);
    }

    // Anular una factura registrada por error (se conserva, no se borra)
    public function anularFacturaProveedora(Request $request, $id)
    {
        $factura = $this->consulta()->find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        if ($factura->estado === EstadoFacturaProveedora::Anulada) {
            return $request->expectsJson()
                ? response()->json(['mensaje' => 'La factura ya está anulada'], 409)
                : back()->withErrors(['compra' => 'La factura ya está anulada']);
        }

        $datos = $request->validate(
            ['observaciones' => 'required|string|max:300'],
            ['observaciones.required' => 'Indica el motivo de la anulación.', 'observaciones.max' => 'Máximo :max caracteres.']
        );

        $factura->update(['estado' => EstadoFacturaProveedora::Anulada, 'observaciones' => $datos['observaciones']]);

        $aviso = $factura->movimientos()->exists()
            ? 'La factura generó una entrada de almacén: si el material no llegó, regístralo con un ajuste.'
            : null;

        if (! $request->expectsJson()) {
            return redirect()
                ->route('compra.visualizar', $factura->id)
                ->with('exito', 'Compra ' . $factura->numero_factura . ' anulada.' . ($aviso ? ' ' . $aviso : ''));
        }

        return response()->json(['factura' => $factura, 'aviso' => $aviso]);
    }

    // Eliminar (solo PENDIENTE y sin entrada de almacén). Si ya está pagada, se anula.
    public function eliminarFacturaProveedora($id)
    {
        $factura = $this->consulta()->find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        if ($factura->estado !== EstadoFacturaProveedora::Pendiente || $factura->movimientos()->exists()) {
            return response()->json(['mensaje' => 'No se puede eliminar: está pagada o generó entrada de almacén. Anúlala.'], 409);
        }

        DB::transaction(function () use ($factura) {
            $factura->lineas()->delete();
            $factura->delete();
        });

        return response()->json(['mensaje' => 'Factura eliminada']);
    }

    /* =========================================================
     *  AUXILIARES
     * ========================================================= */

    // Desde el formulario: quita filas vacías, convierte "PORT-1, PORT-2" en lista
    // y el checkbox de entrada en booleano
    private function prepararFormulario(Request $request): void
    {
        if ($request->expectsJson() || ! is_array($request->input('lineas'))) {
            return;
        }
        $lineas = [];
        foreach ($request->input('lineas') as $l) {
            if (empty($l['concepto']) && empty($l['articulo_id'])) {
                continue;
            }
            if (empty($l['concepto']) && ! empty($l['articulo_id'])) {
                $l['concepto'] = Articulo::whereKey($l['articulo_id'])->value('nombre');
            }
            if (isset($l['numeros_serie']) && is_string($l['numeros_serie'])) {
                $ns = array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', $l['numeros_serie']))));
                $l['numeros_serie'] = $ns ?: null;
            }
            $lineas[] = array_filter($l, fn ($v) => $v !== '' && $v !== null);
        }
        $request->merge(['lineas' => $lineas, 'generar_entrada' => $request->boolean('generar_entrada')]);
    }

    // Todas las facturas, o solo las propias si quien entra es LOGISTA
    // (y no tiene además otro rol con acceso completo)
    private function consulta()
    {
        $personaId = Auth::user()->persona_id;
        $roles = Rol::dePersona($personaId);

        $soloPropias = $roles->contains('LOGISTA') && $roles->intersect(['ADMINISTRADOR', 'COMERCIAL'])->isEmpty();

        return FacturaEmpresaProveedora::query()
            ->when($soloPropias, fn ($q) => $q->where('persona_id', $personaId));
    }

    private function reglas(?int $id = null): array
    {
        return [
            'empresa_id'               => 'nullable|required_without:docente_id|integer|exists:empresa,id',
            'docente_id'               => 'nullable|required_without:empresa_id|integer|exists:persona,id',
            'curso_id'                 => 'nullable|integer|exists:curso,id',
            'numero_factura'           => 'required|string|max:30',
            'fecha'                    => 'required|date',
            'fecha_vencimiento'        => 'nullable|date|after_or_equal:fecha',
            'proveedor_cif'            => 'nullable|string|max:15',
            'proveedor_nombre'         => 'nullable|string|max:100',
            'retencion_irpf'           => 'nullable|numeric|min:0',
            'forma_pago'               => ['sometimes', Rule::enum(FormaPago::class)],
            'documento'                => 'nullable|string|max:255',
            'observaciones'            => 'nullable|string|max:300',
            'lineas'                   => 'required|array|min:1',
            'lineas.*.concepto'        => 'required|string|max:200',
            'lineas.*.tipo_coste'      => ['nullable', Rule::enum(TipoCoste::class)],
            'lineas.*.curso_id'        => 'nullable|integer|exists:curso,id',
            'lineas.*.articulo_id'     => 'nullable|integer|exists:articulo,id',
            'lineas.*.cantidad'        => 'nullable|numeric|min:0.01',
            'lineas.*.precio_unitario' => 'required|numeric|min:0',
            'lineas.*.descuento_pct'   => 'nullable|numeric|min:0|max:100',
            'lineas.*.tipo_iva'        => 'nullable|numeric|min:0|max:100',
            'lineas.*.exento'          => ['nullable', Rule::enum(LOPD::class)],
        ];
    }

    // Emisor: empresa con rol PROVEEDOR o FORMADORA, o un docente; nunca los dos.
    // Además, no puede estar ya registrada la misma factura del mismo emisor.
    private function comprobarEmisor(array $datos, Request $request)
    {
        $empresaId = $datos['empresa_id'] ?? null;
        $docenteId = $datos['docente_id'] ?? null;

        if ($empresaId && $docenteId) {
            return response()->json(['mensaje' => 'Indica la empresa o el docente, no los dos'], 422);
        }

        if ($empresaId) {
            $empresa = Empresa::find($empresaId);
            if (! $empresa->tieneRol(RolEmpresa::Proveedor) && ! $empresa->tieneRol(RolEmpresa::Formadora)) {
                return response()->json(['mensaje' => 'La empresa no tiene el rol PROVEEDOR ni FORMADORA'], 422);
            }
        }

        $duplicada = FacturaEmpresaProveedora::where('numero_factura', $datos['numero_factura'])
            ->where($empresaId ? 'empresa_id' : 'docente_id', $empresaId ?: $docenteId)
            ->when($request->route('id'), fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists();

        if ($duplicada) {
            return response()->json(['mensaje' => 'Esa factura de ese proveedor ya está registrada'], 422);
        }

        return null;
    }

    private function guardarLineas(FacturaEmpresaProveedora $factura, array $lineas): void
    {
        foreach ($lineas as $linea) {
            unset($linea['numeros_serie']);
            $factura->lineas()->create(array_filter($linea, fn ($v) => $v !== null));
        }
        $factura->recalcularTotales();
    }

    // ENTRADA_COMPRA con las líneas que llevan artículo
    private function generarEntrada(FacturaEmpresaProveedora $factura, array $lineas, int $almacenId): void
    {
        $lineasMovimiento = [];

        foreach ($lineas as $linea) {
            if (empty($linea['articulo_id'])) {
                continue;
            }
            $cantidad = (int) round((float) ($linea['cantidad'] ?? 1));
            $coste = (float) $linea['precio_unitario'] * (1 - (float) ($linea['descuento_pct'] ?? 0) / 100);

            if (! empty($linea['numeros_serie'])) {
                if (count($linea['numeros_serie']) !== $cantidad) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'lineas' => "En «{$linea['concepto']}» hay $cantidad unidades pero " . count($linea['numeros_serie']) . ' números de serie.',
                    ]);
                }
                // Una línea de movimiento por unidad
                foreach ($linea['numeros_serie'] as $ns) {
                    $lineasMovimiento[] = ['articulo_id' => $linea['articulo_id'], 'cantidad' => 1, 'numero_serie' => $ns, 'coste_unitario' => $coste];
                }
            } else {
                $lineasMovimiento[] = ['articulo_id' => $linea['articulo_id'], 'cantidad' => $cantidad, 'coste_unitario' => $coste];
            }
        }

        if (empty($lineasMovimiento)) {
            return;
        }

        $this->almacenService->registrarMovimiento([
            'tipo'                          => TipoMovimiento::EntradaCompra,
            'almacen_destino_id'            => $almacenId,
            'proveedor_id'                  => $factura->empresa_id,
            'factura_empresa_proveedora_id' => $factura->id,
            'persona_id'                    => $factura->persona_id,
            'referencia'                    => $factura->numero_factura,
        ], $lineasMovimiento);
    }
}