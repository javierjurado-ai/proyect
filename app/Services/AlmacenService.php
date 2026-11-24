<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Enums\EstadoSerie;
use App\Models\Enums\LOPD;
use App\Models\Enums\TipoMovimiento;
use App\Models\MovimientoAlmacen;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class AlmacenService
{
  
    public function registrarMovimiento(array $cabecera, array $lineas): MovimientoAlmacen
    {
        $tipo = $cabecera['tipo'] instanceof TipoMovimiento
            ? $cabecera['tipo']
            : TipoMovimiento::from($cabecera['tipo']);

        $origen  = $cabecera['almacen_origen_id'] ?? null;
        $destino = $cabecera['almacen_destino_id'] ?? null;

        $this->comprobarAlmacenes($tipo, $origen, $destino);

        if (empty($lineas)) {
            throw ValidationException::withMessages(['lineas' => 'El movimiento no tiene líneas.']);
        }

        return DB::transaction(function () use ($tipo, $cabecera, $lineas, $origen, $destino) {
            $movimiento = MovimientoAlmacen::create(array_merge($cabecera, ['tipo' => $tipo]));

            foreach ($lineas as $i => $linea) {
                $articulo = Articulo::find($linea['articulo_id'] ?? null);
                if (! $articulo) {
                    throw ValidationException::withMessages(["lineas.$i.articulo_id" => 'El artículo no existe.']);
                }

                $cantidad = (int) ($linea['cantidad'] ?? 0);
                if ($cantidad < 1) {
                    throw ValidationException::withMessages(["lineas.$i.cantidad" => 'La cantidad debe ser al menos 1.']);
                }

                $serieId = null;
                if ($articulo->controla_serie === LOPD::Si) {
                    if ($cantidad !== 1) {
                        throw ValidationException::withMessages([
                            "lineas.$i.cantidad" => "«{$articulo->nombre}» se controla por nº de serie: una línea por unidad (cantidad 1).",
                        ]);
                    }
                    $serieId = $this->moverUnidad($tipo, $articulo, $linea, $i, $origen, $destino, $cabecera['curso_id'] ?? null);
                }

                if ($origen) {
                    $this->restarStock($origen, $articulo, $cantidad, $i);
                }
                if ($destino) {
                    $this->sumarStock($destino, $articulo->id, $cantidad);
                }

                $movimiento->lineas()->create([
                    'articulo_id'       => $articulo->id,
                    'articulo_serie_id' => $serieId,
                    'cantidad'          => $cantidad,
                    'coste_unitario'    => $linea['coste_unitario'] ?? $articulo->precio_coste ?? 0,
                ]);
            }

            return $movimiento->load('lineas.articulo', 'lineas.unidad');
        });
    }

    // Qué almacenes necesita cada tipo de movimiento
    private function comprobarAlmacenes(TipoMovimiento $tipo, $origen, $destino): void
    {
        $error = match ($tipo) {
            TipoMovimiento::EntradaCompra, TipoMovimiento::DevolucionCurso
                => (! $destino || $origen) ? 'Este movimiento solo lleva almacén de destino.' : null,
            TipoMovimiento::SalidaCurso, TipoMovimiento::Baja
                => (! $origen || $destino) ? 'Este movimiento solo lleva almacén de origen.' : null,
            TipoMovimiento::Traspaso
                => (! $origen || ! $destino) ? 'Un traspaso necesita almacén de origen y de destino.'
                   : ($origen == $destino ? 'El origen y el destino no pueden ser el mismo almacén.' : null),
            TipoMovimiento::Ajuste
                => ((bool) $origen === (bool) $destino)
                   ? 'Un ajuste lleva almacén de origen (resta) o de destino (suma), pero no los dos.' : null,
        };

        if ($error) {
            throw ValidationException::withMessages(['almacen' => $error]);
        }
    }

    private function restarStock(int $almacenId, Articulo $articulo, int $cantidad, int $i): void
    {
        $fila = Stock::where('almacen_id', $almacenId)
            ->where('articulo_id', $articulo->id)
            ->lockForUpdate()
            ->first();

        $disponible = $fila?->cantidad ?? 0;
        if ($disponible < $cantidad) {
            throw ValidationException::withMessages([
                "lineas.$i.cantidad" => "No hay stock suficiente de «{$articulo->nombre}»: hay $disponible y se piden $cantidad.",
            ]);
        }

        Stock::where('almacen_id', $almacenId)
            ->where('articulo_id', $articulo->id)
            ->update(['cantidad' => $disponible - $cantidad]);
    }

    private function sumarStock(int $almacenId, int $articuloId, int $cantidad): void
    {
        $fila = Stock::where('almacen_id', $almacenId)
            ->where('articulo_id', $articuloId)
            ->lockForUpdate()
            ->first();

        if ($fila) {
            Stock::where('almacen_id', $almacenId)
                ->where('articulo_id', $articuloId)
                ->update(['cantidad' => $fila->cantidad + $cantidad]);
        } else {
            Stock::create(['almacen_id' => $almacenId, 'articulo_id' => $articuloId, 'cantidad' => $cantidad]);
        }
    }

    // Comprueba y actualiza la unidad con nº de serie. Devuelve su id.
    private function moverUnidad(TipoMovimiento $tipo, Articulo $articulo, array $linea, int $i, $origen, $destino, $cursoId): int
    {
        $campo = "lineas.$i.articulo_serie_id";

        // Alta de una unidad nueva: entrada de compra o ajuste positivo con numero_serie
        if (empty($linea['articulo_serie_id']) && ! empty($linea['numero_serie'])) {
            if (! in_array($tipo, [TipoMovimiento::EntradaCompra, TipoMovimiento::Ajuste], true) || ! $destino) {
                throw ValidationException::withMessages([$campo => 'Solo se dan de alta unidades nuevas en una entrada o un ajuste positivo.']);
            }
            if (ArticuloSerie::where('articulo_id', $articulo->id)->where('numero_serie', $linea['numero_serie'])->exists()) {
                throw ValidationException::withMessages(["lineas.$i.numero_serie" => "El nº de serie {$linea['numero_serie']} ya existe."]);
            }

            return ArticuloSerie::create([
                'articulo_id'  => $articulo->id,
                'numero_serie' => $linea['numero_serie'],
                'almacen_id'   => $destino,
                'estado'       => EstadoSerie::Disponible,
                'fecha_alta'   => today(),
            ])->id;
        }

        $unidad = ArticuloSerie::lockForUpdate()->find($linea['articulo_serie_id'] ?? null);
        if (! $unidad || $unidad->articulo_id != $articulo->id) {
            throw ValidationException::withMessages([$campo => "Indica una unidad (nº de serie) de «{$articulo->nombre}»."]);
        }

        $ns = $unidad->numero_serie;

        // Las que salen de un almacén tienen que estar en él
        if ($origen && $unidad->almacen_id != $origen) {
            throw ValidationException::withMessages([$campo => "La unidad $ns no está en el almacén de origen."]);
        }

        switch ($tipo) {
            case TipoMovimiento::SalidaCurso:
                if ($unidad->estado !== EstadoSerie::Disponible) {
                    throw ValidationException::withMessages([$campo => "La unidad $ns no está disponible."]);
                }
                $unidad->fill(['almacen_id' => null, 'curso_id' => $cursoId, 'estado' => EstadoSerie::Prestado]);
                break;

            case TipoMovimiento::DevolucionCurso:
                if ($unidad->estado !== EstadoSerie::Prestado || ($cursoId && $unidad->curso_id != $cursoId)) {
                    throw ValidationException::withMessages([$campo => "La unidad $ns no está prestada a este curso."]);
                }
                $unidad->fill(['almacen_id' => $destino, 'curso_id' => null, 'estado' => EstadoSerie::Disponible]);
                break;

            case TipoMovimiento::Traspaso:
                if ($unidad->estado === EstadoSerie::Prestado || $unidad->estado === EstadoSerie::Baja) {
                    throw ValidationException::withMessages([$campo => "La unidad $ns no se puede traspasar."]);
                }
                $unidad->almacen_id = $destino;
                break;

            case TipoMovimiento::Baja:
                $unidad->fill(['almacen_id' => null, 'curso_id' => null, 'estado' => EstadoSerie::Baja]);
                break;

            case TipoMovimiento::Ajuste:
                $origen
                    ? $unidad->fill(['almacen_id' => null, 'curso_id' => null, 'estado' => EstadoSerie::Baja])
                    : $unidad->fill(['almacen_id' => $destino, 'curso_id' => null, 'estado' => EstadoSerie::Disponible]);
                break;

            case TipoMovimiento::EntradaCompra:
                if ($unidad->estado !== EstadoSerie::Baja && $unidad->almacen_id !== null) {
                    throw ValidationException::withMessages([$campo => "La unidad $ns ya está en un almacén."]);
                }
                $unidad->fill(['almacen_id' => $destino, 'curso_id' => null, 'estado' => EstadoSerie::Disponible]);
                break;
        }

        $unidad->save();

        return $unidad->id;
    }
}
