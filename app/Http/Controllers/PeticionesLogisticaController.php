<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
use App\Services\AlmacenService;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeticionesLogisticaController extends Controller
{
    public function __construct(private AlmacenService $almacenService)
    {
    }

    public function index(Request $request){
        $personaId = Auth::user()->persona_id;
        $rol     = Rol::where('persona_id', $personaId)->value('rol')?->value;
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
    public function servirPeticion(Request $request, $id)
    {
        $peticion = PeticionesLogistica::with('peticionProductos.articulo')->findOrFail($id);

        $datos = $request->validate($this->reglasMovimiento() + [
            'transportista' => 'nullable|string|max:45',
            'seguimiento'   => 'nullable|string|max:45',
        ]);

        if (in_array($peticion->estado, [EstadoLogistica::Cancelada, EstadoLogistica::Completada, EstadoLogistica::Devuelta], true)) {
            return response()->json(['mensaje' => 'La petición está ' . strtolower($peticion->estado->value)], 409);
        }
        if (! $peticion->curso_id || ! $peticion->almacen_id) {
            return response()->json(['mensaje' => 'La petición necesita curso y almacén para servirse'], 422);
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

            $this->almacenService->registrarMovimiento([
                'tipo'                    => TipoMovimiento::SalidaCurso,
                'almacen_origen_id'       => $peticion->almacen_id,
                'curso_id'                => $peticion->curso_id,
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
            $peticion->estado = $pendiente ? EstadoLogistica::EnProceso : EstadoLogistica::Entregada;
            $peticion->save();

            return $peticion;
        });

        return response()->json($peticion->fresh(['peticionProductos.articulo', 'movimientos']));
    }

    // Devuelve el material retornable: crea una DEVOLUCION_CURSO.
    // Sin "lineas" devuelve todo lo pendiente. Por defecto vuelve al almacén de la petición.
    // lineas: [{peticion_producto_id, cantidad, articulo_serie_ids?: []}]
    public function devolverPeticion(Request $request, $id)
    {
        $peticion = PeticionesLogistica::with('peticionProductos.articulo')->findOrFail($id);

        $datos = $request->validate($this->reglasMovimiento() + [
            'almacen_id' => 'nullable|integer|exists:almacen,id',
        ]);

        $almacenId = $datos['almacen_id'] ?? $peticion->almacen_id;
        if (! $almacenId) {
            return response()->json(['mensaje' => 'Indica el almacén al que vuelve el material'], 422);
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

        return response()->json($peticion->fresh(['peticionProductos.articulo', 'movimientos']));
    }

    /* =========================================================
     *  AUXILIARES
     * ========================================================= */

    private function reglas(): array
    {
        return [
            'curso_id'                  => 'nullable|integer|exists:curso,id',
            'almacen_id'                => 'nullable|integer|exists:almacen,id',
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
