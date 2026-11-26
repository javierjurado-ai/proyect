<?php

namespace App\Http\Controllers;

use App\Models\Coste;
use App\Models\CreditoFormativo;
use App\Models\Empresa;
use App\Models\Enums\RolEmpresa;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class CreditoFormativoController extends Controller
{
    /* =========================================================
     *  PANTALLA WEB (ADMINISTRADOR y COMERCIAL)
     * ========================================================= */

    // Listado de créditos de un año (vista credito/index.blade.php). ?anio=2026
    public function index(Request $request)
    {
        $anioActual = today()->year;
        $anio = $request->integer('anio', $anioActual);
        if ($anio < 2000 || $anio > 2100) {
            $anio = $anioActual;
        }

        $creditos = CreditoFormativo::with('empresa')->where('año', $anio)->get();
        $imputado = $this->imputadoEnCursos($anio);

        // Una fila por crédito, ordenadas por lo que le queda a cada empresa (oportunidades arriba)
        $filas = $creditos
            ->map(fn (CreditoFormativo $c) => $this->fila($c, $imputado))
            ->sortByDesc('disponible')
            ->values();

        // Clientes activos que todavía no tienen crédito registrado ese año
        $empresasSinCredito = Empresa::conRol(RolEmpresa::Cliente)
            ->where('activo', true)
            ->whereNotIn('id', $creditos->pluck('empresa_id'))
            ->orderBy('nombre')
            ->get();

        // Años para el selector: los que tienen créditos + el actual y el siguiente
        $anios = CreditoFormativo::query()->distinct()->pluck('año')
            ->map(fn ($a) => (int) $a)
            ->merge([$anioActual, $anioActual + 1, $anio])
            ->unique()->sortDesc()->values();

        // El crédito caduca el 31 de diciembre
        $diasParaCaducar = $anio === $anioActual
            ? (int) today()->diffInDays(Carbon::create($anio, 12, 31)->startOfDay())
            : null;

        $resumen = [
            'total'      => $filas->sum('total'),
            'consumido'  => $filas->sum('consumido'),
            'disponible' => $filas->sum('disponible'),
        ];
        $resumen['porcentaje'] = $resumen['total'] > 0 ? round(100 * $resumen['consumido'] / $resumen['total']) : 0;

        return view('credito.index', compact(
            'anio', 'anioActual', 'anios', 'filas', 'empresasSinCredito', 'diasParaCaducar', 'resumen'
        ));
    }

    // Formulario para registrar un crédito (?empresa_id=3&anio=2026 lo deja preseleccionado)
    public function crearFormulario(Request $request)
    {
        return view('credito.formulario', [
            'empresas'          => $this->opcionesEmpresas(),
            'empresaPorDefecto' => $request->integer('empresa_id') ?: null,
            'anioPorDefecto'    => $request->integer('anio') ?: today()->year,
        ]);
    }

    // Guarda el crédito desde el formulario web
    public function guardarCredito(Request $request)
    {
        $datos = $this->validarWeb($request);

        CreditoFormativo::create($datos);

        return redirect()
            ->route('credito.index', ['anio' => $datos['año']])
            ->with('exito', 'Crédito formativo registrado correctamente.');
    }

    // Formulario de edición, con lo imputado en cursos de ese año como referencia
    public function editarFormulario($id)
    {
        $credito = CreditoFormativo::with('empresa')->findOrFail($id);
        $fila    = $this->fila($credito, $this->imputadoEnCursos((int) $credito->año, $credito->empresa_id));

        return view('credito.formulario', [
            'credito'  => $credito,
            'fila'     => $fila,
            'empresas' => $this->opcionesEmpresas($credito->empresa),
        ]);
    }

    // Actualiza el crédito desde el formulario web
    public function actualizarCreditoWeb(Request $request, $id)
    {
        $credito = CreditoFormativo::findOrFail($id);
        $datos   = $this->validarWeb($request, $credito->id);

        $credito->update($datos);

        return redirect()
            ->route('credito.index', ['anio' => $datos['año']])
            ->with('exito', 'Crédito formativo actualizado correctamente.');
    }

    // Elimina el crédito desde la tabla web
    public function eliminarCreditoWeb($id)
    {
        $credito = CreditoFormativo::findOrFail($id);
        $anio    = $credito->año;

        try {
            $credito->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('credito.index', ['anio' => $anio])
                ->withErrors(['credito' => 'No se puede eliminar: el crédito tiene datos relacionados.']);
        }

        return redirect()
            ->route('credito.index', ['anio' => $anio])
            ->with('exito', 'Crédito formativo eliminado.');
    }

    /* =========================================================
     *  API REST (JSON)
     * ========================================================= */

    // Listar todos los créditos formativos
    public function listarCredito()
    {
        return response()->json(CreditoFormativo::all());
    }

    // Obtener un crédito formativo por id
    public function obtenerCredito($id)
    {
        $credito = CreditoFormativo::find($id);

        if (! $credito) {
            return response()->json(['mensaje' => 'Crédito formativo no encontrado'], 404);
        }

        return response()->json($credito);
    }

    // Crear un crédito formativo
    public function crearCredito(Request $request)
    {
        $datos = $request->validate($this->reglas());

        $credito = CreditoFormativo::create($datos);

        return response()->json($credito->fresh(), 201);
    }

    // Actualizar un crédito formativo
    public function actualizarCredito(Request $request, $id)
    {
        $credito = CreditoFormativo::find($id);

        if (! $credito) {
            return response()->json(['mensaje' => 'Crédito formativo no encontrado'], 404);
        }

        $datos = $request->validate($this->reglas());

        $credito->update($datos);

        return response()->json($credito);
    }

    // Eliminar un crédito formativo
    public function eliminarCredito($id)
    {
        $credito = CreditoFormativo::find($id);

        if (! $credito) {
            return response()->json(['mensaje' => 'Crédito formativo no encontrado'], 404);
        }

        try {
            $credito->delete();
            return response()->json(['mensaje' => 'Crédito formativo eliminado']);
        } catch (QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: el crédito formativo tiene datos relacionados',
            ], 409);
        }
    }

    // Reglas de validación comunes a crear y actualizar
    private function reglas(): array
    {
        return [
            'empresa_id'        => 'required|integer|exists:empresa,id',
            'año'               => 'required|integer|between:2000,2100',
            'importe_total'     => 'required|numeric|min:0|max:99999999.99|decimal:0,2',
            'importe_consumido' => 'sometimes|required|numeric|min:0|max:99999999.99|decimal:0,2|lte:importe_total',
        ];
    }

    /* =========================================================
     *  AUXILIARES DE LA PANTALLA WEB
     * ========================================================= */

    // Valida el formulario web. El campo se llama "anio" en el formulario y "año" en la BD.
    private function validarWeb(Request $request, ?int $id = null): array
    {
        $datos = $request->validate([
            'empresa_id'        => ['required', 'integer', Rule::exists('empresa', 'id')],
            'anio'              => ['required', 'integer', 'between:2000,2100',
                // Un solo crédito por empresa y año
                Rule::unique('credito_formativo', 'año')
                    ->where(fn ($q) => $q->where('empresa_id', $request->input('empresa_id')))
                    ->ignore($id)],
            'importe_total'     => 'required|numeric|min:0|max:99999999.99|decimal:0,2',
            'importe_consumido' => 'nullable|numeric|min:0|max:99999999.99|decimal:0,2|lte:importe_total',
        ], [
            'anio.unique'            => 'Esta empresa ya tiene un crédito registrado para ese año.',
            'importe_consumido.lte'  => 'El importe consumido no puede ser mayor que el crédito total.',
        ], [
            'empresa_id'        => 'empresa',
            'anio'              => 'año',
            'importe_total'     => 'crédito total',
            'importe_consumido' => 'importe consumido',
        ]);

        return [
            'empresa_id'        => $datos['empresa_id'],
            'año'               => $datos['anio'],
            'importe_total'     => $datos['importe_total'],
            'importe_consumido' => $datos['importe_consumido'] ?? 0,
        ];
    }

    // Suma de la tabla coste por empresa, en los cursos que empiezan ese año.
    // OJO: el alias no puede ser "total" porque Coste tiene un accessor getTotalAttribute que lo taparía.
    private function imputadoEnCursos(int $anio, ?int $empresaId = null): Collection
    {
        return Coste::query()
            ->join('curso', 'curso.id', '=', 'coste.curso_id')
            ->whereYear('curso.fecha_inicio', $anio)
            ->when($empresaId, fn ($q) => $q->where('coste.empresa_id', $empresaId))
            ->groupBy('coste.empresa_id')
            ->selectRaw('coste.empresa_id,
                SUM(coste.directos + coste.indirectos + coste.organizacion + coste.salariales) AS importe_cursos,
                COUNT(*) AS cursos')
            ->get()
            ->keyBy('empresa_id');
    }

    // Datos ya calculados de un crédito para la tabla y el formulario
    private function fila(CreditoFormativo $c, Collection $imputado): object
    {
        $total      = (float) $c->importe_total;
        $consumido  = (float) $c->importe_consumido;
        $porcentaje = $total > 0 ? round(100 * $consumido / $total) : 0;
        $enCursos   = (float) ($imputado[$c->empresa_id]->importe_cursos ?? 0);

        $estado = match (true) {
            $total > 0 && $consumido >= $total => ['Agotado', 'rojo'],
            $porcentaje >= 80                  => ['Casi agotado', 'ambar'],
            $consumido <= 0                    => ['Sin usar', 'azul'],
            default                            => ['En uso', 'verde'],
        };

        // Avisos cuando lo guardado no cuadra con los costes de los cursos
        $aviso = match (true) {
            $enCursos > $total + 0.005                => 'Los cursos (' . $this->euros($enCursos) . ') superan el crédito',
            abs($enCursos - $consumido) > 0.005       => 'En cursos hay ' . $this->euros($enCursos) . ' imputados',
            default                                   => null,
        };

        return (object) [
            'credito'    => $c,
            'empresa'    => $c->empresa,
            'total'      => $total,
            'consumido'  => $consumido,
            'disponible' => max(0, $total - $consumido),
            'porcentaje' => $porcentaje,
            'enCursos'   => $enCursos,
            'numCursos'  => (int) ($imputado[$c->empresa_id]->cursos ?? 0),
            'estado'     => $estado,
            'aviso'      => $aviso,
        ];
    }

    // Empresas cliente activas para el desplegable (y la del crédito, aunque ya no lo sea)
    private function opcionesEmpresas(?Empresa $incluir = null): array
    {
        $empresas = Empresa::conRol(RolEmpresa::Cliente)->where('activo', true)->orderBy('nombre')->get();

        if ($incluir && ! $empresas->contains('id', $incluir->id)) {
            $empresas->push($incluir);
        }

        return $empresas->mapWithKeys(fn ($e) => [$e->id => $e->nombre . ' (' . $e->cif . ')'])->all();
    }

    private function euros(float $n): string
    {
        return number_format($n, 2, ',', '.') . ' €';
    }
}