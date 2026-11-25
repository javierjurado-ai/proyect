<?php

namespace App\Http\Controllers;

use App\Models\Cobro;
use App\Models\Empresa;
use App\Models\Enums\EstadoFactura;
use App\Models\Enums\FormaPago;
use App\Models\Enums\LOPD;
use App\Models\Enums\RolEmpresa;
use App\Models\Enums\TipoCoste;
use App\Models\Enums\TipoFactura;
use App\Models\Factura;
use App\Models\SerieFactura;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class FacturaController extends Controller
{
    /* =========================================================
     *  FACTURAS
     * ========================================================= */

    // Listar facturas (?estado=, ?empresa_id=, ?curso_id=, ?anio=)
    public function listarFacturas(Request $request)
    {
        $facturas = Factura::with(['serie', 'empresa', 'curso.formacion'])
            ->withSum('cobros as cobrado', 'importe')
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('empresa_id'), fn ($q) => $q->where('empresa_id', $request->empresa_id))
            ->when($request->filled('curso_id'), fn ($q) => $q->where('curso_id', $request->curso_id))
            ->when($request->filled('anio'), fn ($q) => $q->whereYear('fecha_emision', $request->anio))
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get()
            ->each(fn ($f) => $f->append('numero_completo'));

        return response()->json($facturas);
    }

    // Obtener una factura con líneas y cobros
    public function obtenerFacturaPorID($id)
    {
        $factura = Factura::with(['serie', 'empresa', 'curso.formacion', 'lineas', 'cobros', 'facturaRectificada', 'rectificativas'])
            ->find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }

        return response()->json($factura->append(['numero_completo', 'cobrado', 'pendiente_cobro']));
    }

    // Crear una factura en BORRADOR con sus líneas
    public function agregarFactura(Request $request)
    {
        $datos = $request->validate($this->reglasFactura());

        if (! Empresa::find($datos['empresa_id'])->tieneRol(RolEmpresa::Cliente)) {
            return response()->json(['mensaje' => 'La empresa no tiene el rol CLIENTE'], 422);
        }

        $factura = DB::transaction(function () use ($datos) {
            $lineas = $datos['lineas'];
            unset($datos['lineas']);

            $factura = Factura::create($datos + [
                'tipo'       => TipoFactura::Ordinaria,
                'estado'     => EstadoFactura::Borrador,
                'persona_id' => Auth::user()->persona_id,
            ]);
            $this->guardarLineas($factura, $lineas);

            return $factura;
        });

        return response()->json($factura->load('lineas'), 201);
    }

    // Actualizar un BORRADOR (sustituye todas sus líneas)
    public function actualizarFactura(Request $request, $id)
    {
        $factura = Factura::find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        if ($factura->estado !== EstadoFactura::Borrador) {
            return response()->json(['mensaje' => 'Solo se puede modificar una factura en borrador. Para corregir una emitida, haz una rectificativa.'], 409);
        }

        $reglas = $this->reglasFactura();
        if ($factura->tipo === TipoFactura::Rectificativa) {
            unset($reglas['lineas.*.cantidad']);
            $reglas['lineas.*.cantidad'] = 'nullable|numeric|not_in:0'; // admite negativas
        }
        $datos = $request->validate($reglas);

        DB::transaction(function () use ($factura, $datos) {
            $lineas = $datos['lineas'];
            unset($datos['lineas']);

            $factura->update($datos);
            $factura->lineas()->delete();
            $this->guardarLineas($factura, $lineas);
        });

        return response()->json($factura->fresh('lineas'));
    }

    // Eliminar una factura (solo BORRADOR)
    public function eliminarFactura($id)
    {
        $factura = Factura::find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        if ($factura->estado !== EstadoFactura::Borrador) {
            return response()->json(['mensaje' => 'Una factura emitida no se puede eliminar. Haz una rectificativa.'], 409);
        }

        DB::transaction(function () use ($factura) {
            $factura->lineas()->delete();
            $factura->delete();
        });

        return response()->json(['mensaje' => 'Factura eliminada']);
    }

    // Emitir un borrador: asigna número de la serie, copia los datos del cliente,
    // calcula vencimiento y totales, y la deja cerrada.
    public function emitirFactura(Request $request, $id)
    {
        $datos = $request->validate(['fecha_emision' => 'nullable|date']);

        $factura = Factura::with('lineas', 'empresa')->find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        if ($factura->estado !== EstadoFactura::Borrador) {
            return response()->json(['mensaje' => 'La factura ya está emitida'], 409);
        }
        if ($factura->lineas->isEmpty()) {
            return response()->json(['mensaje' => 'La factura no tiene líneas'], 422);
        }

        $fecha = Carbon::parse($datos['fecha_emision'] ?? today());

        $factura = DB::transaction(function () use ($factura, $fecha) {
            // Bloquea la serie para que dos emisiones a la vez no cojan el mismo número
            $serie = SerieFactura::lockForUpdate()->find($factura->serie_factura_id);

            if ($serie->anio !== $fecha->year) {
                abort(response()->json([
                    'mensaje' => "La serie {$serie->codigo}-{$serie->anio} no corresponde al año de emisión ({$fecha->year}). Crea la serie de ese año.",
                ], 422));
            }

            $serie->increment('ultimo_numero');
            $empresa = $factura->empresa;

            $factura->fill([
                'numero'            => $serie->ultimo_numero,
                'fecha_emision'     => $fecha,
                'fecha_vencimiento' => $fecha->copy()->addDays($empresa->dias_vencimiento ?? 30),
                'cliente_cif'       => $empresa->cif,
                'cliente_nombre'    => $empresa->nombre,
                'cliente_direccion' => collect([
                    $empresa->direccion,
                    trim(($empresa->codigo_postal ?? '') . ' ' . ($empresa->municipio ?? '')),
                    $empresa->provincia,
                ])->filter()->implode(', ') ?: null,
                'estado'            => EstadoFactura::Emitida,
            ]);
            $factura->recalcularTotales();

            // Huella encadenada con la última factura emitida.
            // OJO: no es el formato oficial de VeriFactu; habrá que adaptarlo cuando sea obligatorio.
            $anterior = Factura::whereNotNull('hash')->where('id', '!=', $factura->id)->orderByDesc('id')->value('hash');
            $factura->hash_anterior = $anterior;
            $factura->hash = hash('sha256', implode('|', [
                $factura->fresh('serie')->numero_completo,
                $fecha->toDateString(),
                $factura->cliente_cif,
                number_format((float) $factura->total, 2, '.', ''),
                $anterior ?? '',
            ]));
            $factura->save();

            // Al emitir una rectificativa, la original queda RECTIFICADA
            if ($factura->tipo === TipoFactura::Rectificativa && $factura->factura_rectificada_id) {
                Factura::whereKey($factura->factura_rectificada_id)->update(['estado' => EstadoFactura::Rectificada]);
            }

            return $factura;
        });

        return response()->json($factura->fresh(['serie', 'lineas'])->append('numero_completo'));
    }

    // Crea una RECTIFICATIVA en borrador con las líneas de la original en negativo.
    // Después se edita (si es una corrección parcial) y se emite.
    public function rectificarFactura(Request $request, $id)
    {
        $datos = $request->validate([
            'motivo_rectificacion' => 'required|string|max:200',
            'serie_factura_id'     => 'nullable|integer|exists:serie_factura,id',
        ]);

        $original = Factura::with('lineas')->find($id);

        if (! $original) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        if (! in_array($original->estado, [EstadoFactura::Emitida, EstadoFactura::CobradaParcial, EstadoFactura::Cobrada], true)) {
            return response()->json(['mensaje' => 'Solo se puede rectificar una factura emitida y no rectificada'], 409);
        }

        // Por defecto, la serie R del año en curso
        $serieId = $datos['serie_factura_id']
            ?? SerieFactura::where('codigo', 'R')->where('anio', today()->year)->value('id');

        if (! $serieId) {
            return response()->json(['mensaje' => 'No existe la serie R de ' . today()->year . '. Créala primero.'], 422);
        }

        $rectificativa = DB::transaction(function () use ($original, $datos, $serieId) {
            $rect = Factura::create([
                'serie_factura_id'       => $serieId,
                'tipo'                   => TipoFactura::Rectificativa,
                'factura_rectificada_id' => $original->id,
                'motivo_rectificacion'   => $datos['motivo_rectificacion'],
                'empresa_id'             => $original->empresa_id,
                'curso_id'               => $original->curso_id,
                'forma_pago'             => $original->forma_pago,
                'estado'                 => EstadoFactura::Borrador,
                'persona_id'             => Auth::user()->persona_id,
            ]);

            foreach ($original->lineas as $linea) {
                $rect->lineas()->create(array_merge(
                    $linea->only(['concepto', 'tipo_coste', 'curso_id', 'articulo_id', 'precio_unitario',
                                  'descuento_pct', 'tipo_iva', 'exento', 'motivo_exencion']),
                    ['cantidad' => -1 * (float) $linea->cantidad]
                ));
            }
            $rect->recalcularTotales();

            return $rect;
        });

        return response()->json($rectificativa->load('lineas'), 201);
    }

    /* =========================================================
     *  COBROS
     * ========================================================= */

    // Registrar un cobro (admite cobros parciales)
    public function registrarCobro(Request $request, $id)
    {
        $factura = Factura::find($id);

        if (! $factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        if (! in_array($factura->estado, [EstadoFactura::Emitida, EstadoFactura::CobradaParcial], true)) {
            return response()->json(['mensaje' => 'Solo se cobran facturas emitidas pendientes de cobro'], 409);
        }

        $datos = $request->validate([
            'fecha'      => 'required|date',
            'importe'    => 'required|numeric|min:0.01',
            'forma_pago' => ['required', Rule::enum(FormaPago::class)],
            'referencia' => 'nullable|string|max:60',
        ]);

        if ($datos['importe'] > $factura->pendiente_cobro + 0.001) {
            return response()->json(['mensaje' => "El importe supera lo pendiente de cobro ({$factura->pendiente_cobro} €)"], 422);
        }

        $cobro = DB::transaction(function () use ($factura, $datos) {
            $cobro = $factura->cobros()->create($datos);
            $this->actualizarEstadoCobro($factura);

            return $cobro;
        });

        return response()->json(['cobro' => $cobro, 'factura' => $factura->fresh()->append(['cobrado', 'pendiente_cobro'])], 201);
    }

    // Eliminar un cobro registrado por error
    public function eliminarCobro($id, $cobroId)
    {
        $cobro = Cobro::where('factura_id', $id)->find($cobroId);

        if (! $cobro) {
            return response()->json(['mensaje' => 'Cobro no encontrado'], 404);
        }

        $factura = $cobro->factura;
        if ($factura->estado === EstadoFactura::Rectificada) {
            return response()->json(['mensaje' => 'La factura está rectificada'], 409);
        }

        DB::transaction(function () use ($cobro, $factura) {
            $cobro->delete();
            $this->actualizarEstadoCobro($factura);
        });

        return response()->json(['mensaje' => 'Cobro eliminado', 'factura' => $factura->fresh()->append(['cobrado', 'pendiente_cobro'])]);
    }

    /* =========================================================
     *  PENDIENTE DE FACTURAR
     * ========================================================= */

    // Cursos y empresas (grupo_empresa) sin factura ordinaria emitida,
    // con el importe facturable (directos + indirectos + organización; sin salariales)
    public function pendientesFacturar()
    {
        $pendientes = DB::table('grupo_empresa as ge')
            ->join('curso as c', 'c.id', '=', 'ge.curso_id')
            ->join('empresa as e', 'e.id', '=', 'ge.empresa_id')
            ->leftJoin('formacion as f', 'f.id', '=', 'c.formacion_id')
            ->leftJoin('coste as co', fn ($j) => $j->on('co.curso_id', '=', 'ge.curso_id')->on('co.empresa_id', '=', 'ge.empresa_id'))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('factura as fa')
                ->whereColumn('fa.curso_id', 'ge.curso_id')
                ->whereColumn('fa.empresa_id', 'ge.empresa_id')
                ->where('fa.tipo', TipoFactura::Ordinaria->value)
                ->whereNotIn('fa.estado', [EstadoFactura::Borrador->value, EstadoFactura::Rectificada->value]))
            ->select([
                'ge.curso_id', 'c.codigo_accion', 'c.codigo_grupo', 'f.nombre as formacion', 'c.fecha_fin',
                'ge.empresa_id', 'e.nombre as empresa',
                'co.directos', 'co.indirectos', 'co.organizacion',
                DB::raw('COALESCE(co.directos,0) + COALESCE(co.indirectos,0) + COALESCE(co.organizacion,0) AS importe_facturable'),
                'co.salariales as salariales_no_facturables',
            ])
            ->orderBy('c.fecha_fin')
            ->get();

        return response()->json($pendientes);
    }

    /* =========================================================
     *  SERIES
     * ========================================================= */

    public function listarSeries()
    {
        return response()->json(SerieFactura::orderByDesc('anio')->orderBy('codigo')->get());
    }

    // Crear una serie (p. ej. F y R de cada año nuevo)
    public function agregarSerie(Request $request)
    {
        $datos = $request->validate([
            'codigo'      => ['required', 'string', 'max:10',
                Rule::unique('serie_factura', 'codigo')->where(fn ($q) => $q->where('anio', $request->anio))],
            'descripcion' => 'required|string|max:60',
            'anio'        => 'required|integer|min:2000|max:2100',
        ], [
            'codigo.unique' => 'Ya existe esa serie para ese año.',
        ]);

        return response()->json(SerieFactura::create($datos + ['ultimo_numero' => 0]), 201);
    }

    /* =========================================================
     *  AUXILIARES
     * ========================================================= */

    private function reglasFactura(): array
    {
        return [
            'serie_factura_id'         => 'required|integer|exists:serie_factura,id',
            'empresa_id'               => 'required|integer|exists:empresa,id',
            'curso_id'                 => 'nullable|integer|exists:curso,id',
            'forma_pago'               => ['sometimes', Rule::enum(FormaPago::class)],
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
            'lineas.*.motivo_exencion' => 'nullable|string|max:150|required_if:lineas.*.exento,SI',
        ];
    }

    // Crea las líneas (base y cuota las calcula el modelo) y recalcula los totales
    private function guardarLineas(Factura $factura, array $lineas): void
    {
        foreach ($lineas as $linea) {
            $factura->lineas()->create(array_filter($linea, fn ($v) => $v !== null));
        }
        $factura->recalcularTotales();
    }

    private function actualizarEstadoCobro(Factura $factura): void
    {
        $cobrado = (float) $factura->cobros()->sum('importe');

        $factura->estado = match (true) {
            $cobrado <= 0                              => EstadoFactura::Emitida,
            $cobrado + 0.001 >= (float) $factura->total => EstadoFactura::Cobrada,
            default                                     => EstadoFactura::CobradaParcial,
        };
        $factura->save();
    }
}
