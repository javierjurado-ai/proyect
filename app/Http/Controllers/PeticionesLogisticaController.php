<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Almacen;
use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Curso;
use App\Models\PeticionesLogistica;
use App\Models\Enums\EstadoLogistica;
use App\Models\Enums\EstadoSerie;
use App\Models\Enums\LOPD;
use App\Models\Enums\TipoMovimiento;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Stock;
use App\Services\AlmacenService;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeticionesLogisticaController extends Controller
{
    use Concerns\DatosUsuario;

    public function __construct(private AlmacenService $almacenService)
    {
    }

    public function index(Request $request){
        $personaId = Auth::user()->persona_id;
        $rol     = Rol::principal($personaId);
        $personas = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);
        $nombre = collect([$personas->nombre, $personas->primer_apellido, $personas->segundo_apellido])
            ->filter()
            ->implode(' ');

        if (! $rol || ! $personas) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['usuario' => 'No tienes acceso a la página.']);
        }
        $logistica = PeticionesLogistica::all();

        return view('logistica.index', compact('logistica', 'rol', 'nombre','personas'));
    }

    /* =========================================================
     *  API REST
     * ========================================================= */

    // Listar peticiones (?estado=, ?curso_id=, ?almacen_id=)
    public function listarPeticionesLogistica(Request $request)
    {
        $peticiones = PeticionesLogistica::with(['curso.formacion', 'almacen', 'centro', 'peticionProductos.articulo'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('curso_id'), fn ($q) => $q->where('curso_id', $request->curso_id))
            ->when($request->filled('almacen_id'), fn ($q) => $q->where('almacen_id', $request->almacen_id))
            ->when($request->tipo === 'traspaso', fn ($q) => $q->whereNotNull('almacen_destino_id'))
            ->when($request->tipo === 'curso', fn ($q) => $q->whereNull('almacen_destino_id'))
            ->orderBy('fecha_necesaria')
            ->get();

        return response()->json($peticiones);
    }

    public function obtenerPeticionLogisticaPorID($id)
    {
        $peticion = PeticionesLogistica::with([
            'curso.formacion', 'solicitante', 'almacen', 'centro',
            'peticionProductos.articulo', 'movimientos.lineas.unidad',
        ])->findOrFail($id);

        return response()->json($peticion, 200);
    }

    // Crear una petición con sus productos.
    // productos: [{articulo_id, cantidad}]
    public function agregarPeticionLogistica(Request $request)
    {
        $datos = $request->validate($this->reglas());

        $peticion = DB::transaction(function () use ($datos) {
            $productos = $datos['productos'] ?? [];
            unset($datos['productos']);

            $peticion = PeticionesLogistica::create($this->completarDesdeCurso($datos) + [
                'solicitante_id' => Auth::user()->persona_id,
                'estado'         => $datos['estado'] ?? EstadoLogistica::Pendiente,
            ]);

            foreach ($productos as $producto) {
                $peticion->peticionProductos()->create($producto);
            }

            return $peticion;
        });

        return response()->json($peticion->load('peticionProductos.articulo'), 201);
    }

    // Actualizar una petición. Los productos solo se pueden cambiar
    // mientras no se haya servido nada; si se envían, sustituyen a los actuales.
    public function actualizarPeticionLogistica($id, Request $request)
    {
        $peticion = PeticionesLogistica::findOrFail($id);

        $datos = $request->validate($this->reglas());

        $yaServida = $peticion->peticionProductos()->where('cantidad_servida', '>', 0)->exists();
        if (array_key_exists('productos', $datos) && $yaServida) {
            return response()->json([
                'mensaje' => 'No se pueden cambiar los productos: parte del material ya se ha servido',
            ], 409);
        }

        DB::transaction(function () use ($peticion, $datos) {
            $productos = $datos['productos'] ?? null;
            unset($datos['productos']);

            $peticion->update($datos);

            if ($productos !== null) {
                $peticion->peticionProductos()->delete();
                foreach ($productos as $producto) {
                    $peticion->peticionProductos()->create($producto);
                }
            }
        });

        return response()->json($peticion->fresh('peticionProductos.articulo'), 200);
    }

    public function eliminarPeticionLogistica($id)
    {
        $peticion = PeticionesLogistica::findOrFail($id);

        if ($peticion->movimientos()->exists()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: ya tiene salidas o devoluciones de almacén. Puedes cancelarla.',
            ], 409);
        }

        try {
            DB::transaction(function () use ($peticion) {
                $peticion->peticionProductos()->delete();
                $peticion->delete();
            });
            return response()->json(['mensaje' => 'peticion logistica eliminada'], 200);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: tiene cursos asociados',
            ], 409);
        }
    }

    /* =========================================================
     *  SERVIR Y DEVOLVER MATERIAL
     * ========================================================= */

    // Sirve el material: crea una SALIDA_CURSO desde el almacén de la petición.
    // Sin "lineas" sirve todo lo pendiente; para los artículos con nº de serie
    // coge unidades disponibles del almacén, salvo que se indiquen en articulo_serie_ids.
    // lineas: [{peticion_producto_id, cantidad, articulo_serie_ids?: []}]
    public function servirPeticion(Request $request, $id)
    {
        $peticion = PeticionesLogistica::with('peticionProductos.articulo')->findOrFail($id);

        if ($error = $this->quitarLineasVacias($request)) {
            return $error;
        }

        $datos = $request->validate($this->reglasMovimiento() + [
            'transportista' => 'nullable|string|max:45',
            'seguimiento'   => 'nullable|string|max:45',
        ]);

        if (in_array($peticion->estado, [EstadoLogistica::Cancelada, EstadoLogistica::Completada, EstadoLogistica::Devuelta], true)) {
            return $this->error($request, 'La petición está ' . strtolower($peticion->estado->value), 409);
        }
        if (! $peticion->almacen_id || (! $peticion->curso_id && ! $peticion->esTraspaso())) {
            return $this->error($request, 'La petición necesita almacén y un curso (o un almacén de destino) para servirse', 422);
        }

        $peticion = DB::transaction(function () use ($peticion, $datos) {
            [$lineasMov, $porLinea] = $this->prepararLineas(
                $peticion,
                $datos['lineas'] ?? null,
                fn ($p) => $p->cantidad - $p->cantidad_servida,
                fn ($articulo, $cantidad) => ArticuloSerie::where('articulo_id', $articulo->id)
                    ->where('almacen_id', $peticion->almacen_id)
                    ->where('estado', EstadoSerie::Disponible->value)
                    ->orderBy('id')->limit($cantidad)->pluck('id')->all(),
                'servir'
            );

            // Traspaso: el material pasa del almacén de origen al de destino en un solo paso
            $this->almacenService->registrarMovimiento([
                'tipo'                    => $peticion->esTraspaso() ? TipoMovimiento::Traspaso : TipoMovimiento::SalidaCurso,
                'almacen_origen_id'       => $peticion->almacen_id,
                'almacen_destino_id'      => $peticion->almacen_destino_id,
                'curso_id'                => $peticion->esTraspaso() ? null : $peticion->curso_id,
                'peticiones_logistica_id' => $peticion->id,
                'persona_id'              => Auth::user()->persona_id,
                'referencia'              => $datos['seguimiento'] ?? null,
                'observaciones'           => $datos['observaciones'] ?? null,
            ], $lineasMov);

            foreach ($porLinea as $productoId => $cantidad) {
                $peticion->peticionProductos()->whereKey($productoId)->increment('cantidad_servida', $cantidad);
            }

            $pendiente = $peticion->peticionProductos()->whereColumn('cantidad_servida', '<', 'cantidad')->exists();
            $peticion->fill(array_filter([
                'transportista' => $datos['transportista'] ?? null,
                'seguimiento'   => $datos['seguimiento'] ?? null,
            ]));
            // Un traspaso servido del todo queda COMPLETADO (no se devuelve)
            $peticion->estado = $pendiente
                ? EstadoLogistica::EnProceso
                : ($peticion->esTraspaso() ? EstadoLogistica::Completada : EstadoLogistica::Entregada);
            $peticion->save();

            return $peticion;
        });

        return $this->ok($request, $peticion->fresh(['peticionProductos.articulo', 'movimientos']),
            $peticion->esTraspaso() ? 'Traspaso enviado: el material ya está en el almacén de destino.' : 'Material servido.');
    }

    // Devuelve el material retornable: crea una DEVOLUCION_CURSO.
    // Sin "lineas" devuelve todo lo pendiente. Por defecto vuelve al almacén de la petición.
    // lineas: [{peticion_producto_id, cantidad, articulo_serie_ids?: []}]
    public function devolverPeticion(Request $request, $id)
    {
        $peticion = PeticionesLogistica::with('peticionProductos.articulo')->findOrFail($id);

        if ($error = $this->quitarLineasVacias($request)) {
            return $error;
        }

        $datos = $request->validate($this->reglasMovimiento() + [
            'almacen_id' => 'nullable|integer|exists:almacen,id',
        ]);

        if ($peticion->esTraspaso()) {
            return $this->error($request, 'Un traspaso entre almacenes no se devuelve: haz otro traspaso en sentido contrario', 422);
        }

        $almacenId = $datos['almacen_id'] ?? $peticion->almacen_id;
        if (! $almacenId) {
            return $this->error($request, 'Indica el almacén al que vuelve el material', 422);
        }

        $peticion = DB::transaction(function () use ($peticion, $datos, $almacenId) {
            [$lineasMov, $porLinea] = $this->prepararLineas(
                $peticion,
                $datos['lineas'] ?? null,
                fn ($p) => $p->articulo?->retornable === LOPD::Si ? $p->cantidad_servida - $p->cantidad_devuelta : 0,
                fn ($articulo, $cantidad) => ArticuloSerie::where('articulo_id', $articulo->id)
                    ->where('curso_id', $peticion->curso_id)
                    ->where('estado', EstadoSerie::Prestado->value)
                    ->orderBy('id')->limit($cantidad)->pluck('id')->all(),
                'devolver'
            );

            $this->almacenService->registrarMovimiento([
                'tipo'                    => TipoMovimiento::DevolucionCurso,
                'almacen_destino_id'      => $almacenId,
                'curso_id'                => $peticion->curso_id,
                'peticiones_logistica_id' => $peticion->id,
                'persona_id'              => Auth::user()->persona_id,
                'observaciones'           => $datos['observaciones'] ?? null,
            ], $lineasMov);

            foreach ($porLinea as $productoId => $cantidad) {
                $peticion->peticionProductos()->whereKey($productoId)->increment('cantidad_devuelta', $cantidad);
            }

            // DEVUELTA cuando ya no queda nada retornable fuera
            $quedaFuera = $peticion->peticionProductos()
                ->whereColumn('cantidad_devuelta', '<', 'cantidad_servida')
                ->whereHas('articulo', fn ($q) => $q->where('retornable', LOPD::Si->value))
                ->exists();
            if (! $quedaFuera) {
                $peticion->estado = EstadoLogistica::Devuelta;
                $peticion->save();
            }

            return $peticion;
        });

        return $this->ok($request, $peticion->fresh(['peticionProductos.articulo', 'movimientos']), 'Devolución registrada.');
    }

    /* =========================================================
     *  VISTAS WEB DE LOGÍSTICA (pedidos de cursos y traspasos)
     * ========================================================= */

    // Pedidos de material de los cursos (?estado=servir|fuera|cerradas|todas, ?almacen_id=)
    public function vistaPedidos(Request $request)
    {
        [$rol, $nombre] = $this->rolYNombre();
        $filtro = $request->query('estado', 'servir');

        $grupos = [
            'servir'   => ['PENDIENTE', 'EN_PROCESO'],
            'fuera'    => ['ENTREGADA'],
            'cerradas' => ['DEVUELTA', 'COMPLETADA', 'CANCELADA'],
        ];

        $base = PeticionesLogistica::whereNull('almacen_destino_id')
            ->when($request->filled('almacen_id'), fn ($q) => $q->where('almacen_id', $request->almacen_id));

        $contar = fn (array $estados) => (clone $base)->whereIn('estado', $estados)->count();
        $pestanas = [
            'servir'   => ['Por servir', $contar($grupos['servir'])],
            'fuera'    => ['Material fuera', $contar($grupos['fuera'])],
            'cerradas' => ['Cerradas', $contar($grupos['cerradas'])],
            'todas'    => ['Todas', (clone $base)->count()],
        ];

        $peticiones = (clone $base)
            ->with(['curso.formacion', 'centro', 'almacen', 'peticionProductos.articulo'])
            ->when(isset($grupos[$filtro]), fn ($q) => $q->whereIn('estado', $grupos[$filtro]))
            ->orderByRaw('fecha_necesaria IS NULL, fecha_necesaria')
            ->get();

        $almacenes = Almacen::orderBy('nombre')->get();
        $contadores = ['pedidos' => $pestanas['servir'][1] ?: null];

        return view('pedidosLogistica.index', compact('rol', 'nombre', 'peticiones', 'pestanas', 'filtro', 'almacenes', 'contadores'));
    }

    // Ficha de una petición (de curso o traspaso) con los formularios de servir y devolver
    public function vistaPeticion($id)
    {
        [$rol, $nombre] = $this->rolYNombre();

        $peticion = PeticionesLogistica::with([
            'curso.formacion', 'centro', 'almacen', 'almacenDestino', 'solicitante',
            'peticionProductos.articulo', 'movimientos.persona', 'movimientos.lineas.articulo', 'movimientos.lineas.unidad',
        ])->findOrFail($id);

        // Stock en el almacén de la petición y en los demás, por artículo
        $articuloIds = $peticion->peticionProductos->pluck('articulo_id')->filter()->unique();
        $stock = Stock::whereIn('articulo_id', $articuloIds)->with('almacen')->get()->groupBy('articulo_id');

        $lineas = $peticion->peticionProductos->map(function ($p) use ($peticion, $stock) {
            $enAlmacen = (int) optional($stock->get($p->articulo_id)?->firstWhere('almacen_id', $peticion->almacen_id))->cantidad;
            $porServir = max(0, $p->cantidad - $p->cantidad_servida);
            $otros = ($stock->get($p->articulo_id) ?? collect())
                ->filter(fn ($s) => $s->almacen_id != $peticion->almacen_id && $s->cantidad > 0)
                ->values();

            return [
                'producto'      => $p,
                'enAlmacen'     => $enAlmacen,
                'porServir'     => $porServir,
                'falta'         => max(0, $porServir - $enAlmacen),
                'porDevolver'   => $p->articulo?->retornable === LOPD::Si ? max(0, $p->cantidad_servida - $p->cantidad_devuelta) : 0,
                'otrosAlmacenes'=> $otros,
            ];
        });

        $puedeServir = in_array($peticion->estado->value, ['PENDIENTE', 'EN_PROCESO'], true) && $lineas->sum('porServir') > 0;
        $puedeDevolver = ! $peticion->esTraspaso() && $lineas->sum('porDevolver') > 0;

        return view('pedidosLogistica.visualizar', compact('rol', 'nombre', 'peticion', 'lineas', 'puedeServir', 'puedeDevolver'));
    }

    // Traspasos entre almacenes: los que me piden (salen de mi almacén) y los que he pedido (llegan a él)
    public function vistaTraspasos(Request $request)
    {
        [$rol, $nombre] = $this->rolYNombre();

        $misAlmacenes = $this->misAlmacenes();
        $almacen = $misAlmacenes->firstWhere('id', (int) $request->query('almacen_id')) ?? $misAlmacenes->first();
        $lado = $request->query('lado') === 'pedido' ? 'pedido' : 'mepiden';

        $consulta = fn () => PeticionesLogistica::whereNotNull('almacen_destino_id')
            ->with(['almacen', 'almacenDestino', 'curso.formacion', 'solicitante', 'peticionProductos.articulo']);

        $mePiden = $almacen ? $consulta()->where('almacen_id', $almacen->id)->orderByRaw("estado = 'COMPLETADA', fecha_necesaria")->get() : collect();
        $hePedido = $almacen ? $consulta()->where('almacen_destino_id', $almacen->id)->orderByRaw("estado = 'COMPLETADA', fecha_necesaria")->get() : collect();

        $traspasos = $lado === 'pedido' ? $hePedido : $mePiden;
        $porEnviar = $mePiden->whereIn('estado', [EstadoLogistica::Pendiente, EstadoLogistica::EnProceso])->count();
        $enCamino = $hePedido->whereIn('estado', [EstadoLogistica::Pendiente, EstadoLogistica::EnProceso])->count();

        return view('traspasos.index', compact('rol', 'nombre', 'misAlmacenes', 'almacen', 'lado', 'traspasos', 'porEnviar', 'enCamino'));
    }

    // Formulario para pedir material a otro almacén.
    // Admite ?origen=, ?destino=, ?articulo_id=, ?cantidad=, ?curso_id= para venir rellenado desde una petición
    public function formularioTraspaso(Request $request)
    {
        [$rol, $nombre] = $this->rolYNombre();

        $almacenes = Almacen::where('activo', true)->orderBy('nombre')->get();
        $destino = (int) $request->query('destino', $this->misAlmacenes()->first()?->id);
        $origen = (int) $request->query('origen', $almacenes->firstWhere('id', '!=', $destino)?->id);
        $articulos = Articulo::where('activo', true)->orderBy('nombre')->get();
        $stock = Stock::all()->groupBy('articulo_id')->map(fn ($filas) => $filas->pluck('cantidad', 'almacen_id'));
        $cursos = Curso::with('formacion')->whereDate('fecha_fin', '>=', today())->orderBy('fecha_inicio')->get();

        $inicial = [
            'articulo_id' => $request->query('articulo_id'),
            'cantidad'    => $request->query('cantidad'),
            'curso_id'    => $request->query('curso_id'),
        ];

        return view('traspasos.formulario', compact('rol', 'nombre', 'almacenes', 'origen', 'destino', 'articulos', 'stock', 'cursos', 'inicial'));
    }

    // Guarda el traspaso pedido desde el formulario
    public function guardarTraspaso(Request $request)
    {
        // Quita las filas vacías del formulario
        $request->merge(['productos' => array_values(array_filter(
            $request->input('productos', []),
            fn ($p) => ! empty($p['articulo_id']) && (int) ($p['cantidad'] ?? 0) > 0
        ))]);

        $datos = $request->validate([
            'almacen_id'              => 'required|integer|exists:almacen,id',
            'almacen_destino_id'      => 'required|integer|exists:almacen,id|different:almacen_id',
            'curso_id'                => 'nullable|integer|exists:curso,id',
            'fecha_necesaria'         => 'nullable|date',
            'observaciones'           => 'nullable|string|max:200',
            'productos'               => 'required|array|min:1',
            'productos.*.articulo_id' => 'required|integer|exists:articulo,id|distinct',
            'productos.*.cantidad'    => 'required|integer|min:1',
        ], [
            'almacen_destino_id.different' => 'El almacén de origen y el de destino no pueden ser el mismo.',
            'productos.required'           => 'Añade al menos un artículo con su cantidad.',
            'productos.*.articulo_id.distinct' => 'Has repetido un artículo: pon toda la cantidad en una sola fila.',
        ]);

        // Avisa si el almacén de origen no tiene tanto ahora mismo
        foreach ($datos['productos'] as $i => $p) {
            $hay = (int) Stock::where('almacen_id', $datos['almacen_id'])->where('articulo_id', $p['articulo_id'])->value('cantidad');
            if ($p['cantidad'] > $hay) {
                $articulo = Articulo::find($p['articulo_id']);
                throw ValidationException::withMessages([
                    "productos.$i.cantidad" => "El almacén de origen solo tiene $hay de «{$articulo->nombre}».",
                ]);
            }
        }

        DB::transaction(function () use ($datos) {
            $peticion = PeticionesLogistica::create([
                'almacen_id'         => $datos['almacen_id'],
                'almacen_destino_id' => $datos['almacen_destino_id'],
                'curso_id'           => $datos['curso_id'] ?? null,
                'solicitante_id'     => Auth::user()->persona_id,
                'fecha'              => today(),
                'fecha_necesaria'    => $datos['fecha_necesaria'] ?? null,
                'estado'             => EstadoLogistica::Pendiente,
                'observaciones'      => $datos['observaciones'] ?? null,
            ]);
            foreach ($datos['productos'] as $p) {
                $peticion->peticionProductos()->create($p);
            }
        });

        return redirect()
            ->route('traspaso.index', ['almacen_id' => $datos['almacen_destino_id'], 'lado' => 'pedido'])
            ->with('exito', 'Petición enviada. El otro almacén la verá en «Me piden».');
    }

    /* =========================================================
     *  AUXILIARES
     * ========================================================= */

    private function reglas(): array
    {
        return [
            'curso_id'                  => 'nullable|integer|exists:curso,id',
            'almacen_id'                => 'nullable|required_with:almacen_destino_id|integer|exists:almacen,id',
            'almacen_destino_id'        => 'nullable|integer|exists:almacen,id|different:almacen_id',
            'centro_id'                 => 'nullable|integer|exists:centro,id',
            'fecha'                     => ['required', 'date'],
            'fecha_necesaria'           => 'nullable|date',
            'fecha_devolucion_prevista' => 'nullable|date',
            'estado'                    => ['sometimes', 'required', Rule::enum(EstadoLogistica::class)],
            'observaciones'             => ['nullable', 'string', 'max:200'],
            'direccion_envio'           => 'nullable|string|max:150',
            'transportista'             => 'nullable|string|max:45',
            'seguimiento'               => 'nullable|string|max:45',
            'coste_envio'               => 'nullable|numeric|min:0',
            'productos'                 => 'sometimes|array',
            'productos.*.articulo_id'   => 'required|integer|exists:articulo,id',
            'productos.*.cantidad'      => 'required|integer|min:1',
        ];
    }

    // Desde un formulario llegan todas las líneas, también las que se dejan a 0: se quitan.
    // Si todas estaban a 0 no se hace nada (sin líneas se serviría todo lo pendiente).
    private function quitarLineasVacias(Request $request)
    {
        if (! is_array($request->input('lineas'))) {
            return null;
        }
        $lineas = array_values(array_filter($request->input('lineas'), fn ($l) => (int) ($l['cantidad'] ?? 0) > 0));
        if (empty($lineas)) {
            return $this->error($request, 'Indica la cantidad de al menos un artículo', 422);
        }
        $request->merge(['lineas' => $lineas]);

        return null;
    }

    // Respuesta de éxito: JSON para la API, o volver a la página con un aviso si viene de un formulario
    private function ok(Request $request, $datos, string $mensaje, int $codigo = 200)
    {
        return $request->expectsJson()
            ? response()->json($datos, $codigo)
            : back()->with('exito', $mensaje);
    }

    private function error(Request $request, string $mensaje, int $codigo)
    {
        return $request->expectsJson()
            ? response()->json(['mensaje' => $mensaje], $codigo)
            : back()->withErrors(['peticion' => $mensaje]);
    }

    private function reglasMovimiento(): array
    {
        return [
            'observaciones'                 => 'nullable|string|max:200',
            'lineas'                        => 'sometimes|array|min:1',
            'lineas.*.peticion_producto_id' => 'required|integer|exists:peticion_producto,id',
            'lineas.*.cantidad'             => 'required|integer|min:1',
            'lineas.*.articulo_serie_ids'   => 'nullable|array',
            'lineas.*.articulo_serie_ids.*' => 'integer|exists:articulo_serie,id',
        ];
    }

    // Si la petición tiene curso, completa centro y fechas que falten con los del curso
    private function completarDesdeCurso(array $datos): array
    {
        if (empty($datos['curso_id'])) {
            return $datos;
        }
        $curso = Curso::find($datos['curso_id']);

        return $datos + array_filter([
            'centro_id'                 => $curso->centro_id,
            'fecha_necesaria'           => $curso->fecha_inicio,
            'fecha_devolucion_prevista' => $curso->fecha_fin,
        ]);
    }

    // Convierte las líneas de la petición en líneas de movimiento.
    // $pendienteDe: cuánto queda por servir/devolver de cada línea.
    // $unidadesAuto: qué unidades con nº de serie coger si no se indican.
    // Devuelve [líneas del movimiento, cantidad por peticion_producto_id].
    private function prepararLineas(PeticionesLogistica $peticion, ?array $lineas, callable $pendienteDe, callable $unidadesAuto, string $accion): array
    {
        $productos = $peticion->peticionProductos->keyBy('id');

        // Sin líneas: todo lo pendiente
        if ($lineas === null) {
            $lineas = $productos->map(fn ($p) => ['peticion_producto_id' => $p->id, 'cantidad' => $pendienteDe($p)])
                ->filter(fn ($l) => $l['cantidad'] > 0)->values()->all();
        }

        if (empty($lineas)) {
            throw ValidationException::withMessages(['lineas' => "No queda material por $accion."]);
        }

        $lineasMov = [];
        $porLinea = [];

        foreach ($lineas as $i => $linea) {
            $producto = $productos->get($linea['peticion_producto_id']);
            if (! $producto) {
                throw ValidationException::withMessages(["lineas.$i.peticion_producto_id" => 'La línea no es de esta petición.']);
            }
            $articulo = $producto->articulo;
            if (! $articulo) {
                throw ValidationException::withMessages(["lineas.$i" => "La línea «{$producto->producto}» no tiene artículo del catálogo."]);
            }

            $cantidad = (int) $linea['cantidad'];
            $pendiente = $pendienteDe($producto);
            if ($cantidad > $pendiente) {
                throw ValidationException::withMessages([
                    "lineas.$i.cantidad" => "De «{$articulo->nombre}» solo quedan $pendiente por $accion.",
                ]);
            }

            if ($articulo->controla_serie === LOPD::Si) {
                $ids = $linea['articulo_serie_ids'] ?? $unidadesAuto($articulo, $cantidad);
                if (count($ids) !== $cantidad) {
                    throw ValidationException::withMessages([
                        "lineas.$i.articulo_serie_ids" => "Para $accion $cantidad de «{$articulo->nombre}» hacen falta $cantidad unidades y hay " . count($ids) . '.',
                    ]);
                }
                foreach ($ids as $serieId) {
                    $lineasMov[] = ['articulo_id' => $articulo->id, 'cantidad' => 1, 'articulo_serie_id' => $serieId];
                }
            } else {
                $lineasMov[] = ['articulo_id' => $articulo->id, 'cantidad' => $cantidad];
            }

            $porLinea[$producto->id] = ($porLinea[$producto->id] ?? 0) + $cantidad;
        }

        return [$lineasMov, $porLinea];
    }
}
