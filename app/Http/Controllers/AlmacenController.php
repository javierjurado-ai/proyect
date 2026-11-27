<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\ArticuloSerie;
use App\Models\Centro;
use App\Models\Enums\EstadoSerie;
use App\Models\MovimientoAlmacen;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Stock;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlmacenController extends Controller
{
    use Concerns\DatosUsuario;

    /* =========================================================
     *  VISTAS WEB: ALMACENES
     *  (el catálogo de artículos va aparte, en ArticuloController)
     * ========================================================= */

    // Almacén elegido (?almacen_id=): su stock y sus últimos movimientos, y la lista de todos los almacenes
    public function index(Request $request)
    {
        [$rol, $nombre] = $this->rolYNombre();

        // Para el selector, solo los activos; en la tabla de abajo salen todos
        $almacenes = Almacen::where('activo', true)->orderBy('nombre')->get();
        $almacen = $almacenes->firstWhere('id', (int) $request->query('almacen_id'))
            ?? $this->misAlmacenes()->first();

        // Lo que hay en este almacén (solo artículos con cantidad)
        $stock = $almacen
            ? Stock::with('articulo')->where('almacen_id', $almacen->id)->where('cantidad', '>', 0)->get()
                ->sortBy(fn (Stock $s) => $s->articulo?->nombre)->values()
            : collect();

        // Equipos con nº de serie en este almacén, por artículo y estado
        $unidades = $almacen
            ? ArticuloSerie::where('almacen_id', $almacen->id)
                ->whereIn('estado', [EstadoSerie::Disponible->value, EstadoSerie::EnReparacion->value])
                ->selectRaw('articulo_id, estado, COUNT(*) as n')->groupBy('articulo_id', 'estado')->get()
            : collect();
        $disponibles  = $unidades->where('estado', EstadoSerie::Disponible)->pluck('n', 'articulo_id');
        $enReparacion = $unidades->where('estado', EstadoSerie::EnReparacion)->pluck('n', 'articulo_id');

        $movimientos = $almacen
            ? MovimientoAlmacen::with(['lineas.articulo', 'curso.formacion', 'almacenOrigen', 'almacenDestino', 'persona'])
                ->where(fn ($q) => $q->where('almacen_origen_id', $almacen->id)->orWhere('almacen_destino_id', $almacen->id))
                ->orderByDesc('fecha')->orderByDesc('id')->limit(10)->get()
            : collect();

        $resumen = [
            'articulos'    => $stock->count(),
            'unidades'     => (int) $stock->sum('cantidad'),
            'disponibles'  => (int) $disponibles->sum(),
            'enReparacion' => (int) $enReparacion->sum(),
        ];

        // Todos los almacenes (también los inactivos) con lo que tienen guardado
        $todos = Almacen::with(['centro', 'responsable'])
            ->withSum('stock as unidades_total', 'cantidad')
            ->orderByDesc('activo')->orderBy('nombre')->get();

        return view('almacen.index', compact(
            'rol', 'nombre', 'almacenes', 'almacen', 'stock', 'disponibles', 'enReparacion', 'movimientos', 'resumen', 'todos'
        ));
    }

    public function crearFormulario()
    {
        [$rol, $nombre] = $this->rolYNombre();

        return view('almacen.formulario', compact('rol', 'nombre') + $this->desplegables());
    }

    public function guardar(Request $request)
    {
        $datos = $this->validarWeb($request);

        $almacen = Almacen::create($datos);

        return redirect()->route('almacen.index', ['almacen_id' => $almacen->id])
            ->with('exito', 'Almacén «' . $almacen->nombre . '» creado.');
    }

    public function editarFormulario($id)
    {
        [$rol, $nombre] = $this->rolYNombre();
        $almacen = Almacen::findOrFail($id);

        return view('almacen.formulario', compact('rol', 'nombre', 'almacen') + $this->desplegables());
    }

    public function actualizar(Request $request, $id)
    {
        $almacen = Almacen::findOrFail($id);

        $datos = $this->validarWeb($request);

        // No se puede desactivar un almacén que todavía guarda material
        if (! $datos['activo'] && $almacen->activo && $this->tieneMaterial($almacen)) {
            return back()->withInput()->withErrors([
                'activo' => 'No se puede desactivar: el almacén todavía tiene stock o equipos. Traspásalos antes a otro almacén.',
            ]);
        }

        $almacen->update($datos);

        return redirect()->route('almacen.index', $almacen->activo ? ['almacen_id' => $almacen->id] : [])
            ->with('exito', 'Almacén «' . $almacen->nombre . '» actualizado.');
    }

    public function eliminar($id)
    {
        $almacen = Almacen::findOrFail($id);

        if ($this->tieneMaterial($almacen)) {
            return back()->withErrors(['almacen' => 'No se puede eliminar «' . $almacen->nombre . '»: todavía tiene stock o equipos.']);
        }

        try {
            Stock::where('almacen_id', $almacen->id)->delete(); // filas a 0
            $almacen->delete();
        } catch (QueryException $e) {
            return back()->withErrors([
                'almacen' => 'No se puede eliminar «' . $almacen->nombre . '»: tiene movimientos o peticiones. Puedes desactivarlo.',
            ]);
        }

        return redirect()->route('almacen.index')->with('exito', 'Almacén eliminado.');
    }

    // ¿Le queda stock o algún equipo con nº de serie?
    private function tieneMaterial(Almacen $almacen): bool
    {
        return $almacen->stock()->where('cantidad', '>', 0)->exists()
            || $almacen->unidades()->where('estado', '!=', EstadoSerie::Baja->value)->exists();
    }

    private function validarWeb(Request $request): array
    {
        $datos = $request->validate($this->reglas(), [
            'required'              => 'Este campo es obligatorio.',
            'max'                   => 'Máximo :max caracteres.',
            'centro_id.exists'      => 'El centro elegido no existe.',
            'responsable_id.exists' => 'La persona elegida no existe.',
        ]);
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }

    private function desplegables(): array
    {
        $centros = Centro::orderBy('nombre')->pluck('nombre', 'id');

        // Responsable: personal con acceso a la aplicación (logistas primero)
        $responsables = Persona::whereIn('id', DB::table('rol_persona')
                ->join('rol', 'rol.id', '=', 'rol_persona.rol_id')
                ->whereIn('rol.rol', Rol::ROLES_DE_ACCESO)
                ->select('rol_persona.persona_id'))
            ->orderBy('nombre')->get()
            ->mapWithKeys(fn ($p) => [$p->id => trim($p->nombre . ' ' . $p->primer_apellido . ' ' . $p->segundo_apellido)]);

        return compact('centros', 'responsables');
    }

    /* =========================================================
     *  API JSON
     * ========================================================= */

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
