<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\AyudasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Facturas a empresas cliente y sus cobros.
 *
 * Reproduce la lógica de FacturaController: las facturas se crean en BORRADOR y al EMITIRLAS
 * reciben número de su serie y año, copia de los datos del cliente, vencimiento y la huella
 * encadenada con la última factura emitida. Se emiten en orden cronológico para que
 * numeración y cadena de huellas sean correctas.
 *
 * Situaciones que quedan para probar: cobradas, cobro parcial, emitida pendiente, emitida vencida,
 * borrador, rectificada + rectificativa, y un curso terminado pendiente de facturar.
 */
class FacturacionSeeder extends Seeder
{
    use AyudasSeeder;

    private const EXENCION = 'Exenta de IVA (art. 20.Uno.9º Ley 37/1992)';

    private int $emisor;
    private ?string $ultimaHuella = null;

    public function run(): void
    {
        $this->emisor = $this->personaId(PersonaSeeder::PERSONAL['admin'][5]);

        $f1 = $this->finCurso(1001, 1);   // PRL
        $f2 = $this->finCurso(1002, 1);   // Manipulador
        $f3 = $this->finCurso(1003, 1);   // Excel
        $f4 = $this->finCurso(1006, 1);   // Carretillas
        $f5 = $this->finCurso(1007, 1);   // Primeros auxilios

        // Cada entrada: día de emisión => qué hacer. Se ordenan por fecha antes de emitir.
        $plan = [];

        // PRL: las tres empresas, cobradas. Transportes compró además los manuales (con IVA).
        $plan[] = [$f1 + 5, fn () => $this->cobrar($this->emitirNueva('TRANS', 1001, 1, $f1 + 5, [
            ['MD-PRL', 6, 'Manual de PRL nivel básico (material entregado)'],
        ]), $f1 + 33, 'Transferencia TRANS-' . $this->anio($f1) . '-0912')];
        $plan[] = [$f1 + 5, fn () => $this->cobrar($this->emitirNueva('CONS', 1001, 1, $f1 + 5), $f1 + 62, 'Transferencia EBRO 44120')];
        $plan[] = [$f1 + 5, fn () => $this->cobrar($this->emitirNueva('TALL', 1001, 1, $f1 + 5), $f1 + 25, 'Transferencia RUIZ 0087')];

        // Manipulador: se facturaron 12 alumnos en vez de 10 => rectificativa y factura correcta, cobrada
        $plan[] = [$f2 + 4, function () use ($f2) {
            $original = $this->borrador('HOTEL', 1002, 1, 'Facturados 12 participantes', 1.2);
            $this->emitir($original, $f2 + 4);
            $this->rectificar($original, $f2 + 14, 'Error en el número de participantes: eran 10, no 12');
            $this->cobrar($this->emitirNueva('HOTEL', 1002, 1, $f2 + 14), $f2 + 14 + 45, 'Transferencia HMS-' . $this->anio($f2) . '-3381');
        }];

        // Carretillas: Supermercados emitida y VENCIDA; Transportes sin facturar (sale en "pendiente de facturar")
        $plan[] = [$f4 + 4, fn () => $this->emitirNueva('SUPER', 1006, 1, $f4 + 4)];

        // Primeros auxilios: emitida, todavía dentro de plazo
        $plan[] = [$f5 + 5, fn () => $this->emitirNueva('SUPER', 1007, 1, $f5 + 5)];

        // Excel: cobrada, cobro parcial (domiciliación) y pendiente
        $plan[] = [$f3 + 5, fn () => $this->cobrar($this->emitirNueva('ASES', 1003, 1, $f3 + 5), $f3 + 19, 'Transferencia MA-2210')];
        $plan[] = [$f3 + 5, function () use ($f3) {
            $id = $this->emitirNueva('DENT', 1003, 1, $f3 + 5, [], 'DOMICILIACION');
            $total = (float) DB::table('factura')->where('id', $id)->value('total');
            $this->cobrar($id, $f3 + 15, 'Recibo domiciliado 1/2', round($total / 2, 2), 'DOMICILIACION');
        }];
        $plan[] = [$f3 + 5, fn () => $this->emitirNueva('SUPER', 1003, 1, $f3 + 5)];

        usort($plan, fn ($a, $b) => $a[0] <=> $b[0]);
        foreach ($plan as [$dia, $accion]) {
            if ($dia <= 0) {
                $accion();
            }
        }

        // Curso en marcha: factura preparada en BORRADOR (sin número ni huella)
        $this->borrador('ASES', 1005, 1, 'Pendiente de emitir al terminar el curso');
    }

    /* ===================================================================== */

    private function emitirNueva(string $cliente, int $accion, int $grupo, int $dia, array $material = [], string $formaPago = 'TRANSFERENCIA'): int
    {
        $id = $this->borrador($cliente, $accion, $grupo, null, 1.0, $material, $formaPago);
        $this->emitir($id, $dia);

        return $id;
    }

    /**
     * Factura en BORRADOR con los costes bonificables del curso para esa empresa
     * (directos, indirectos y organización; los salariales no se facturan) y, si se indica, material con IVA.
     */
    private function borrador(string $cliente, int $accion, int $grupo, ?string $obs = null, float $factor = 1.0,
                              array $material = [], string $formaPago = 'TRANSFERENCIA'): int
    {
        $empresaId = $this->empresaId($cliente);
        $cursoId   = $this->cursoId($accion, $grupo);
        $coste     = DB::table('coste')->where('curso_id', $cursoId)->where('empresa_id', $empresaId)->first();
        $nombre    = FormacionSeeder::FORMACIONES[$accion][0];

        $facturaId = $this->insertar('factura', [
            'serie_factura_id'  => $this->serie('F', $this->hoy()->year),   // se ajusta al año de emisión al emitir
            'numero'            => null,
            'tipo'              => 'ORDINARIA',
            'empresa_id'        => $empresaId,
            'curso_id'          => $cursoId,
            'forma_pago'        => $formaPago,
            'estado'            => 'BORRADOR',
            'persona_id'        => $this->emisor,
            'observaciones'     => $obs,
        ]);

        $conceptos = [
            ['DIRECTO', 'costes directos', $coste->directos],
            ['INDIRECTO', 'costes indirectos', $coste->indirectos],
            ['ORGANIZACION', 'costes de organización', $coste->organizacion],
        ];
        foreach ($conceptos as [$tipo, $texto, $importe]) {
            $this->linea($facturaId, [
                'concepto' => "Formación «{$nombre}» (acción {$accion}, grupo {$grupo}): {$texto}",
                'tipo_coste' => $tipo, 'curso_id' => $cursoId, 'cantidad' => 1,
                'precio_unitario' => round((float) $importe * $factor, 2),
                'tipo_iva' => 0, 'exento' => 'SI', 'motivo_exencion' => self::EXENCION,
            ]);
        }

        foreach ($material as [$codigo, $cantidad, $concepto]) {
            $art = DB::table('articulo')->where('codigo', $codigo)->first();
            $this->linea($facturaId, [
                'concepto' => $concepto, 'tipo_coste' => 'OTRO', 'curso_id' => $cursoId, 'articulo_id' => $art->id,
                'cantidad' => $cantidad, 'precio_unitario' => $art->precio_venta, 'tipo_iva' => 21, 'exento' => 'NO',
            ]);
        }

        $this->recalcular($facturaId);

        return $facturaId;
    }

    /** Igual que FacturaLinea::booted(): base y cuota se calculan a partir de cantidad, precio, descuento e IVA. */
    private function linea(int $facturaId, array $l): void
    {
        $l += ['articulo_id' => null, 'descuento_pct' => 0, 'motivo_exencion' => null];
        $base = round((float) $l['cantidad'] * (float) $l['precio_unitario'] * (1 - (float) $l['descuento_pct'] / 100), 2);

        DB::table('factura_linea')->insert($l + [
            'factura_id' => $facturaId,
            'base'       => $base,
            'cuota_iva'  => $l['exento'] === 'SI' ? 0 : round($base * (float) $l['tipo_iva'] / 100, 2),
        ]);
    }

    private function recalcular(int $facturaId): void
    {
        $lineas = DB::table('factura_linea')->where('factura_id', $facturaId);
        $base = round((float) (clone $lineas)->sum('base'), 2);
        $iva  = round((float) (clone $lineas)->sum('cuota_iva'), 2);

        DB::table('factura')->where('id', $facturaId)->update([
            'base_imponible' => $base, 'total_iva' => $iva, 'total' => round($base + $iva, 2),
        ]);
    }

    /** Igual que FacturaController::emitirFactura(). */
    private function emitir(int $facturaId, int $dia): void
    {
        $factura = DB::table('factura')->find($facturaId);
        $empresa = DB::table('empresa')->find($factura->empresa_id);
        $fecha   = $this->hoy()->addDays($dia);

        $codigo  = DB::table('serie_factura')->where('id', $factura->serie_factura_id)->value('codigo');
        $serieId = $this->serie($codigo, $fecha->year);
        DB::table('serie_factura')->where('id', $serieId)->increment('ultimo_numero');
        $serie = DB::table('serie_factura')->find($serieId);

        $numeroCompleto = sprintf('%s-%d-%05d', $serie->codigo, $serie->anio, $serie->ultimo_numero);
        $huella = hash('sha256', implode('|', [
            $numeroCompleto,
            $fecha->toDateString(),
            $empresa->cif,
            number_format((float) $factura->total, 2, '.', ''),
            $this->ultimaHuella ?? '',
        ]));

        DB::table('factura')->where('id', $facturaId)->update([
            'serie_factura_id'  => $serieId,
            'numero'            => $serie->ultimo_numero,
            'fecha_emision'     => $fecha->toDateString(),
            'fecha_vencimiento' => $fecha->copy()->addDays($empresa->dias_vencimiento ?? 30)->toDateString(),
            'cliente_cif'       => $empresa->cif,
            'cliente_nombre'    => $empresa->nombre,
            'cliente_direccion' => collect([
                $empresa->direccion,
                trim(($empresa->codigo_postal ?? '') . ' ' . ($empresa->municipio ?? '')),
                $empresa->provincia,
            ])->filter()->implode(', ') ?: null,
            'estado'            => 'EMITIDA',
            'hash'              => $huella,
            'hash_anterior'     => $this->ultimaHuella,
        ]);

        $this->ultimaHuella = $huella;

        // Al emitir una rectificativa, la original queda RECTIFICADA
        if ($factura->tipo === 'RECTIFICATIVA' && $factura->factura_rectificada_id) {
            DB::table('factura')->where('id', $factura->factura_rectificada_id)->update(['estado' => 'RECTIFICADA']);
        }
    }

    /** Igual que rectificarFactura(): rectificativa con las líneas de la original en negativo, y se emite. */
    private function rectificar(int $originalId, int $dia, string $motivo): void
    {
        $original = DB::table('factura')->find($originalId);

        $rectId = $this->insertar('factura', [
            'serie_factura_id'       => $this->serie('R', $this->hoy()->addDays($dia)->year),
            'tipo'                   => 'RECTIFICATIVA',
            'factura_rectificada_id' => $originalId,
            'motivo_rectificacion'   => $motivo,
            'empresa_id'             => $original->empresa_id,
            'curso_id'               => $original->curso_id,
            'forma_pago'             => $original->forma_pago,
            'estado'                 => 'BORRADOR',
            'persona_id'             => $this->emisor,
        ]);

        foreach (DB::table('factura_linea')->where('factura_id', $originalId)->get() as $l) {
            $this->linea($rectId, [
                'concepto' => $l->concepto, 'tipo_coste' => $l->tipo_coste, 'curso_id' => $l->curso_id,
                'articulo_id' => $l->articulo_id, 'cantidad' => -1 * (float) $l->cantidad,
                'precio_unitario' => $l->precio_unitario, 'descuento_pct' => $l->descuento_pct,
                'tipo_iva' => $l->tipo_iva, 'exento' => $l->exento, 'motivo_exencion' => $l->motivo_exencion,
            ]);
        }
        $this->recalcular($rectId);
        $this->emitir($rectId, $dia);
    }

    /** Igual que registrarCobro() + actualizarEstadoCobro(). Sin importe => cobro total. */
    private function cobrar(int $facturaId, int $dia, string $referencia, ?float $importe = null, string $formaPago = 'TRANSFERENCIA'): void
    {
        if ($dia > 0) {
            return;   // todavía no ha llegado la fecha del cobro
        }

        $total = (float) DB::table('factura')->where('id', $facturaId)->value('total');

        DB::table('cobro')->insert([
            'factura_id' => $facturaId, 'fecha' => $this->fecha($dia), 'importe' => $importe ?? $total,
            'forma_pago' => $formaPago, 'referencia' => $referencia,
        ]);

        $cobrado = (float) DB::table('cobro')->where('factura_id', $facturaId)->sum('importe');
        DB::table('factura')->where('id', $facturaId)->update([
            'estado' => $cobrado + 0.001 >= $total ? 'COBRADA' : 'COBRADA_PARCIAL',
        ]);
    }

    private function serie(string $codigo, int $anio): int
    {
        return $this->idDe('serie_factura', ['codigo' => $codigo, 'anio' => $anio]);
    }

    /** Días desde hoy hasta el fin del curso (negativo si ya terminó). */
    private function finCurso(int $accion, int $grupo): int
    {
        $fin = DB::table('curso')->where('id', $this->cursoId($accion, $grupo))->value('fecha_fin');

        return (int) $this->hoy()->diffInDays(Carbon::parse($fin), false);
    }

    private function anio(int $dia): int
    {
        return $this->hoy()->addDays($dia)->year;
    }
}
