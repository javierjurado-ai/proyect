<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Empresa;
use App\Models\Enums\RolEmpresa;
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
    use Concerns\DatosUsuario;

    /* =========================================================
     *  VISTAS WEB: CATÁLOGO DE ARTÍCULOS
     *  (lo que hay en cada almacén va aparte, en AlmacenController)
     * ========================================================= */

    // Catálogo con su stock total (?buscar=, ?tipo=, ?estado=activos|inactivos|todos)
    public function index(Request $request)
    {
        [$rol, $nombre] = $this->rolYNombre();

        $buscar = trim((string) $request->query('buscar', ''));
        $tipo   = (string) $request->query('tipo', '');
        $estado = (string) $request->query('estado', 'activos');

        // Unidades prestadas a cursos por artículo (las que tienen nº de serie)
        $prestadas = ArticuloSerie::where('estado', EstadoSerie::Prestado->value)
            ->selectRaw('articulo_id, COUNT(*) as n')->groupBy('articulo_id')->pluck('n', 'articulo_id');

        $articulos = Articulo::withSum('stock as stock_total', 'cantidad')
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('codigo', 'like', "%{$buscar}%")))
            ->when($tipo !== '', fn ($q) => $q->where('tipo', $tipo))
            ->when($estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($estado === 'inactivos', fn ($q) => $q->where('activo', false))
            ->orderBy('nombre')->get()
            ->map(fn ($a) => [
                'articulo'   => $a,
                'total'      => (int) $a->stock_total,
                'prestados'  => (int) ($prestadas[$a->id] ?? 0),
                'bajoMinimo' => $a->activo && $a->stock_minimo > 0 && (int) $a->stock_total < $a->stock_minimo,
            ]);

        // Equipos prestados agrupados por curso
        $prestadosPorCurso = ArticuloSerie::with(['articulo', 'curso.formacion'])
            ->where('estado', EstadoSerie::Prestado->value)->orderBy('numero_serie')->get()->groupBy('curso_id');

        // El resumen es de todo el catálogo activo, no solo de lo filtrado
        $activos = Articulo::where('activo', true)->withSum('stock as stock_total', 'cantidad')->get();
        $resumen = [
            'activos'    => $activos->count(),
            'conSerie'   => $activos->filter(fn ($a) => $a->controla_serie === LOPD::Si)->count(),
            'prestados'  => (int) $prestadas->sum(),
            'bajoMinimo' => $activos->filter(fn ($a) => $a->stock_minimo > 0 && (int) $a->stock_total < $a->stock_minimo)->count(),
        ];

        $tipos = $this->opciones(TipoArticulo::class);

        return view('articulo.index', compact(
            'rol', 'nombre', 'articulos', 'prestadosPorCurso', 'resumen', 'tipos', 'buscar', 'tipo', 'estado'
        ));
    }

    // Ficha del artículo: sus datos, cuánto hay en cada almacén y sus unidades con nº de serie
    public function visualizar($id)
    {
        [$rol, $nombre] = $this->rolYNombre();

        $articulo = Articulo::with(['proveedorHabitual', 'stock.almacen'])->findOrFail($id);

        $stockPorAlmacen = $articulo->stock->where('cantidad', '>', 0)->sortBy(fn ($s) => $s->almacen?->nombre)->values();

        $unidades = $articulo->unidades()->with(['almacen', 'curso.formacion'])->orderBy('numero_serie')->get();

        return view('articulo.visualizar', compact('rol', 'nombre', 'articulo', 'stockPorAlmacen', 'unidades'));
    }

    public function crearFormulario()
    {
        [$rol, $nombre] = $this->rolYNombre();

        return view('articulo.formulario', compact('rol', 'nombre') + $this->desplegables());
    }

    public function guardar(Request $request)
    {
        $datos = $this->validarWeb($request);

        $articulo = Articulo::create($datos);

        return redirect()->route('articulo.visualizar', $articulo->id)
            ->with('exito', 'Artículo «' . $articulo->nombre . '» creado. El stock entra registrando una compra o un ajuste.');
    }

    public function editarFormulario($id)
    {
        [$rol, $nombre] = $this->rolYNombre();
        $articulo = Articulo::findOrFail($id);

        return view('articulo.formulario', compact('rol', 'nombre', 'articulo') + $this->desplegables());
    }

    public function actualizar(Request $request, $id)
    {
        $articulo = Articulo::findOrFail($id);

        $datos = $this->validarWeb($request, $articulo->id);

        // No se puede cambiar el control por nº de serie si ya hay stock o unidades
        if ($datos['controla_serie'] !== $articulo->controla_serie->value && $this->tieneMovimiento($articulo)) {
            return back()->withInput()->withErrors([
                'controla_serie' => 'No se puede cambiar: el artículo ya tiene stock o unidades con nº de serie.',
            ]);
        }

        $articulo->update($datos);

        return redirect()->route('articulo.visualizar', $articulo->id)
            ->with('exito', 'Artículo «' . $articulo->nombre . '» actualizado.');
    }

    public function eliminar($id)
    {
        $articulo = Articulo::findOrFail($id);

        if ($articulo->stock()->where('cantidad', '>', 0)->exists()) {
            return back()->withErrors(['articulo' => 'No se puede eliminar «' . $articulo->nombre . '»: tiene stock.']);
        }

        try {
            Stock::where('articulo_id', $articulo->id)->delete(); // filas a 0
            $articulo->delete();
        } catch (QueryException $e) {
            return back()->withErrors([
                'articulo' => 'No se puede eliminar «' . $articulo->nombre . '»: tiene movimientos, unidades o peticiones. Puedes desactivarlo.',
            ]);
        }

        return redirect()->route('articulo.index')->with('exito', 'Artículo eliminado.');
    }

    private function tieneMovimiento(Articulo $articulo): bool
    {
        return $articulo->unidades()->exists() || $articulo->stock()->where('cantidad', '>', 0)->exists();
    }

    private function validarWeb(Request $request, ?int $id = null): array
    {
        $reglas = $this->reglas($id);
        // En el formulario estos campos siempre se envían
        // (en la API son opcionales: 'sometimes' → 'required')
        foreach (['unidad', 'retornable', 'controla_serie', 'precio_coste', 'tipo_iva', 'stock_minimo'] as $campo) {
            $lista = is_array($reglas[$campo]) ? $reglas[$campo] : explode('|', $reglas[$campo]);
            $reglas[$campo] = array_map(fn ($r) => $r === 'sometimes' ? 'required' : $r, $lista);
        }

        $datos = $request->validate($reglas, [
            'required'                     => 'Este campo es obligatorio.',
            'max'                          => 'Máximo :max caracteres.',
            'numeric'                      => 'Debe ser un número.',
            'integer'                      => 'Debe ser un número entero.',
            'min'                          => 'No puede ser negativo.',
            // Con Rule::enum el mensaje genérico 'enum' no sirve: hay que poner el campo delante
            'tipo.enum'                    => 'El tipo elegido no es válido.',
            'unidad.enum'                  => 'La unidad elegida no es válida.',
            'retornable.enum'              => 'Elige Sí o No.',
            'controla_serie.enum'          => 'Elige Sí o No.',
            'codigo.unique'                => 'Ya hay otro artículo con ese código.',
            'proveedor_habitual_id.exists' => 'El proveedor elegido no existe.',
        ]);
        $datos['codigo'] = strtoupper(trim($datos['codigo']));
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }

    private function desplegables(): array
    {
        return [
            'tipos'       => $this->opciones(TipoArticulo::class),
            'unidades'    => $this->opciones(UnidadArticulo::class),
            'siNo'        => ['SI' => 'Sí', 'NO' => 'No'],
            'proveedores' => Empresa::conRol(RolEmpresa::Proveedor)->orderBy('nombre')->pluck('nombre', 'id'),
        ];
    }

    // [VALOR => 'Valor'] para los desplegables ('MATERIAL_DIDACTICO' => 'Material didactico')
    private function opciones(string $enum): array
    {
        return collect($enum::cases())
            ->mapWithKeys(fn ($c) => [$c->value => ucfirst(strtolower(str_replace('_', ' ', $c->value)))])
            ->all();
    }

    /* =========================================================
     *  API JSON
     * ========================================================= */

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
