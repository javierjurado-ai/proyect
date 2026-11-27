<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\AyudasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Compras a proveedores, movimientos de almacén, peticiones de material de los cursos,
 * traspasos entre almacenes y tickets de logística.
 *
 * Todo se hace en orden cronológico y con las mismas reglas que AlmacenService:
 * el stock y el estado de cada unidad con nº de serie salen de los movimientos, así que siempre cuadran.
 */
class LogisticaSeeder extends Seeder
{
    use AyudasSeeder;

    private int $logista;
    private int $coordinador;
    private int $admin;

    public function run(): void
    {
        $this->logista     = $this->personaId(PersonaSeeder::PERSONAL['logista'][5]);
        $this->coordinador = $this->personaId(PersonaSeeder::PERSONAL['coordinador'][5]);
        $this->admin       = $this->personaId(PersonaSeeder::PERSONAL['admin'][5]);

        $mad = $this->almacenId(AlmacenSeeder::MADRID);
        $get = $this->almacenId(AlmacenSeeder::GETAFE);

        // ---------------- Compras iniciales ----------------
        $this->compra('INFO', 'SIC/' . $this->anio(-150) . '/0145', -150, 'PAGADA', -125, $mad, [
            ['EQ-PORT', 12, 549.00, 'series' => $this->series('PRT', 1, 12)],
            ['EQ-PROY', 3, 389.00, 'series' => $this->series('PRY', 1, 3)],
            ['EQ-RATON', 20, 9.90],
            ['EQ-TABLET', 6, 229.00, 'series' => $this->series('TAB', 1, 6)],
        ]);
        $this->compra('PAPEL', 'PT-' . $this->anio(-140) . '-00812', -140, 'PAGADA', -110, $mad, [
            ['FU-CUAD', 200, 1.20], ['FU-BOLI', 10, 6.50], ['MD-PRL', 60, 6.00], ['MD-MANIP', 40, 4.50], ['MD-AUX', 25, 5.20],
        ]);
        $this->compra('EQUIP', 'EOP' . $this->anio(-130) . '0331', -130, 'PAGADA', -70, $get, [
            ['OT-EPI', 20, 18.50], ['MO-PIZ', 3, 79.00],
        ]);

        // ---------------- C1: PRL (Madrid) — servido y devuelto ----------------
        $c1 = $this->curso(1001, 1);
        $p = $this->peticion($c1, $mad, -118, [['MD-PRL', 14], ['FU-CUAD', 14], ['EQ-PROY', 1]], 'Material para el curso de PRL');
        $this->servir($p, $this->desde($c1->fecha_inicio, -2));
        $this->devolver($p, $this->desde($c1->fecha_fin, 1));

        // ---------------- C2: Manipulador (Sevilla) — solo fungible, enviado por mensajería ----------------
        $c2 = $this->curso(1002, 1);
        $p = $this->peticion($c2, $mad, -82, [['MD-MANIP', 10], ['FU-CUAD', 10]], 'Envío al hotel', [
            'direccion_envio' => 'Paseo de las Delicias 9, 41013 Sevilla', 'coste_envio' => 14.90]);
        $this->servir($p, $this->desde($c2->fecha_inicio, -3), ['transportista' => 'SEUR', 'seguimiento' => 'SEUR-58213347']);
        $this->estado($p, 'COMPLETADA');

        // ---------------- C4: Carretillas (Getafe) — EPIs prestados y devueltos ----------------
        $c4 = $this->curso(1006, 1);
        $p = $this->peticion($c4, $get, -52, [['OT-EPI', 10]], 'EPIs para las prácticas');
        $this->servir($p, $this->desde($c4->fecha_inicio, -1));
        $this->devolver($p, $this->desde($c4->fecha_fin, 1));

        // ---------------- Compra de reposición (Getafe) ----------------
        $this->compra('INFO', 'SIC/' . $this->anio(-40) . '/0388', -40, 'PAGADA', -10, $get, [
            ['EQ-PORT', 6, 535.00, 'series' => $this->series('PRT', 13, 18)],
            ['EQ-PROY', 2, 375.00, 'series' => $this->series('PRY', 4, 5)],
        ]);

        // ---------------- Traspaso completado: manuales de PRL a Getafe ----------------
        $t = $this->peticion(null, $mad, -38, [['MD-PRL', 20]], 'Manuales para los cursos de PRL de Getafe', ['almacen_destino_id' => $get]);
        $this->servir($t, -37);

        // ---------------- C5: Primeros auxilios (Valencia) — NO devuelto (devolución atrasada) ----------------
        $c5 = $this->curso(1007, 1);
        $p5 = $this->peticion($c5, $mad, -36, [['MD-AUX', 12], ['EQ-PROY', 1], ['EQ-PORT', 2]], 'Material para Valencia', [
            'direccion_envio' => 'C/ Sant Vicent Màrtir 250, 46017 Valencia', 'coste_envio' => 22.50]);
        $this->servir($p5, $this->desde($c5->fecha_inicio, -3), ['transportista' => 'MRW', 'seguimiento' => 'MRW-0461-778120']);

        // ---------------- Compra anulada (registrada dos veces por error; no generó entrada) ----------------
        $this->compra('PAPEL', 'PT-' . $this->anio(-20) . '-00977', -20, 'ANULADA', null, null, [['FU-CUAD', 100, 1.20]],
            'Factura duplicada: es la misma que la PT-00976');

        // ---------------- C6: Inglés (Madrid, en marcha) — tablets prestadas ----------------
        $c6 = $this->curso(1004, 1);
        $p = $this->peticion($c6, $mad, -30, [['EQ-TABLET', 6], ['FU-CUAD', 7]], 'Tablets para las sesiones presenciales');
        $this->servir($p, $this->desde($c6->fecha_inicio, -1));

        // ---------------- Baja y ajuste de inventario ----------------
        $this->movimiento('BAJA', -15, ['origen' => $mad, 'observaciones' => 'Lámpara fundida, reparación no rentable'],
            [['articulo' => 'EQ-PROY', 'cantidad' => 1, 'serie' => $this->unidadesEn('EQ-PROY', $mad, 1)[0]]]);
        $this->movimiento('AJUSTE', -12, ['origen' => $mad, 'observaciones' => 'Recuento de inventario: 3 cuadernos dañados'],
            [['articulo' => 'FU-CUAD', 'cantidad' => 3]]);

        // ---------------- Compras recientes, pendientes de pago ----------------
        $this->compra('PAPEL', 'PT-' . $this->anio(-8) . '-01022', -8, 'PENDIENTE', null, $mad, [
            ['FU-CUAD', 100, 1.15],
            ['concepto' => 'Manual de gestión de alérgenos (edición cliente)', 'cantidad' => 8, 'precio' => 6.50, 'curso' => [1013, 1]],
        ]);
        $this->compra('INFO', 'SIC/' . $this->anio(-5) . '/0412', -5, 'PENDIENTE', null, $mad, [['OT-LIC', 30, 8.00]]);

        // ---------------- C8: Diagnosis (Getafe, en marcha) ----------------
        $c8 = $this->curso(1012, 1);
        $p = $this->peticion($c8, $get, -9, [['EQ-PORT', 6], ['EQ-PROY', 1], ['OT-EPI', 6]], 'Portátiles con el software de diagnosis instalado');
        $this->servir($p, $this->desde($c8->fecha_inicio, -1));

        // ---------------- C9: Atención al cliente (Valencia, en marcha) ----------------
        $c9 = $this->curso(1008, 1);
        $p = $this->peticion($c9, $mad, -7, [['FU-CUAD', 10], ['FU-BOLI', 1]], null, ['coste_envio' => 9.80]);
        $this->servir($p, $this->desde($c9->fecha_inicio, -2), ['transportista' => 'Correos Express', 'seguimiento' => 'CEX7781002541']);

        // ---------------- Peticiones por servir ----------------
        $c10 = $this->curso(1009, 1);
        // Se piden con 1-2 días de margen antes del curso: salen como urgentes en el panel del logista
        $this->peticion($c10, $mad, -4, [['OT-LIC', 9]], 'Licencias para los alumnos de ciberseguridad',
            ['fecha_necesaria' => min($c10->fecha_inicio, $this->fecha(1))]);

        $c11 = $this->curso(1013, 1);
        $p11 = $this->peticion($c11, $mad, -6, [['FU-CUAD', 8], ['producto' => 'Manual de alérgenos (edición cliente)', 'cantidad' => 8]],
            'Los manuales los imprime la papelería', ['direccion_envio' => 'Paseo de las Delicias 9, 41013 Sevilla',
                'fecha_necesaria' => min($c11->fecha_inicio, $this->fecha(2))]);

        // C12: servida en parte (faltan portátiles) => EN_PROCESO
        $c12 = $this->curso(1010, 1);
        $p12 = $this->peticion($c12, $mad, -3, [['EQ-PORT', 6], ['EQ-RATON', 6]], 'Un portátil por alumno');
        $this->servir($p12, -1, [], ['EQ-PORT' => 3, 'EQ-RATON' => 6]);

        $c14 = $this->curso(1001, 2);
        $this->peticion($c14, $get, -2, [['OT-EPI', 9], ['MD-PRL', 9]], null);

        // Cancelada: el curso pasó a ser mixto y ya no hacen falta
        $c15 = $this->curso(1003, 2);
        $p = $this->peticion($c15, $mad, -6, [['EQ-TABLET', 5]], 'Cancelada: el curso será mixto con ordenadores del aula');
        $this->estado($p, 'CANCELADA');

        // Traspaso pendiente de enviar desde Madrid (sale en el panel del logista)
        $this->peticion(null, $mad, -2, [['EQ-PROY', 1]], 'Proyector para el curso de PRL de Getafe',
            ['almacen_destino_id' => $get, 'solicitante_id' => $this->coordinador, 'fecha_necesaria' => $this->fecha(15)]);

        // ---------------- Facturas de docentes y formadoras subcontratadas ----------------
        $this->compraServicio(['docente_id' => $this->personaId(PersonaSeeder::DOCENTES['D5'][5])], 'ERC-' . $this->anio(-20) . '-07',
            -20, 'PAGADA', -5, $this->curso(1003, 1)->id, 'Honorarios docencia «Excel avanzado» (30 h)', 30, 45.00, 15);
        $this->compraServicio(['empresa_id' => $this->empresaId('ITL')], 'ITL-' . $this->anio(-35) . '-118',
            -35, 'PAGADA', -10, $c4->id, 'Impartición del curso «Manejo seguro de carretillas elevadoras»', 1, 1600.00, 0);

        // ---------------- Tickets de logística ----------------
        $this->ticket($p5, $this->coordinador, -2, 'Recogida material Valencia',
            'El curso de primeros auxilios de Valencia terminó hace semanas y todavía no han vuelto el proyector ni los dos portátiles. ¿Programamos la recogida?');
        $this->ticket($p12, $this->logista, -1, 'Faltan 3 portátiles (RGPD)',
            'He servido 3 portátiles y los ratones. Los otros 3 portátiles vuelven del curso de Getafe; los envío en cuanto lleguen.');
        $this->ticket($p11, $this->coordinador, -1, 'Manuales de alérgenos',
            'La papelería confirma que los manuales de alérgenos llegan mañana a primera hora. Enviadlos con los cuadernos.');
    }

    /* =====================================================================
     *  Compras
     * ===================================================================== */

    /**
     * Factura de proveedor con artículos. Si se indica almacén, genera la ENTRADA_COMPRA como la app.
     * Línea con artículo: [codigo, cantidad, precio, 'series' => [...]]. Línea libre: ['concepto', 'cantidad', 'precio', 'curso' => [accion, grupo]].
     */
    private function compra(string $proveedor, string $numero, int $dia, string $estado, ?int $diaPago, ?int $almacen, array $lineas, ?string $obs = null): int
    {
        $empresaId = $this->empresaId($proveedor);
        $fecha = $this->fecha($dia);

        $facturaId = $this->insertar('factura_empresa_proveedora', [
            'empresa_id' => $empresaId, 'docente_id' => null, 'curso_id' => null,
            'numero_factura' => $numero, 'fecha' => $fecha, 'fecha_registro' => $fecha,
            'fecha_vencimiento' => $this->fecha($dia + EmpresaSeeder::EMPRESAS[$proveedor]['vencimiento']),
            'proveedor_cif' => EmpresaSeeder::cif($proveedor), 'proveedor_nombre' => EmpresaSeeder::EMPRESAS[$proveedor]['nombre'],
            'forma_pago' => 'TRANSFERENCIA', 'estado' => $estado,
            'fecha_pago' => $diaPago !== null ? $this->fecha($diaPago) : null,
            'documento' => null, 'persona_id' => $this->logista, 'observaciones' => $obs,
        ]);

        $lineasMov = [];
        foreach ($lineas as $l) {
            if (isset($l['concepto'])) {
                $this->lineaProveedor($facturaId, $l['concepto'], $l['cantidad'], $l['precio'], 21, 'NO', null,
                    isset($l['curso']) ? $this->cursoId(...$l['curso']) : null);
                continue;
            }

            [$codigo, $cantidad, $precio] = $l;
            $this->lineaProveedor($facturaId, AlmacenSeeder::ARTICULOS[$codigo][0], $cantidad, $precio, 21, 'NO', $this->articuloId($codigo), null);

            if (isset($l['series'])) {
                foreach ($l['series'] as $ns) {
                    $lineasMov[] = ['articulo' => $codigo, 'cantidad' => 1, 'nueva' => $ns, 'coste' => $precio];
                }
            } else {
                $lineasMov[] = ['articulo' => $codigo, 'cantidad' => $cantidad, 'coste' => $precio];
            }
        }
        $this->totalesProveedor($facturaId, 0);

        if ($almacen && $lineasMov) {
            $this->movimiento('ENTRADA_COMPRA', $dia, [
                'destino' => $almacen, 'proveedor' => $empresaId, 'factura' => $facturaId, 'referencia' => $numero,
            ], $lineasMov);
        }

        return $facturaId;
    }

    /** Factura de un servicio (docente autónomo o formadora subcontratada): exenta de IVA y con retención si procede. */
    private function compraServicio(array $emisor, string $numero, int $dia, string $estado, ?int $diaPago, int $cursoId,
                                    string $concepto, float $cantidad, float $precio, float $irpfPct): void
    {
        $facturaId = $this->insertar('factura_empresa_proveedora', $emisor + [
            'curso_id' => $cursoId, 'numero_factura' => $numero, 'fecha' => $this->fecha($dia), 'fecha_registro' => $this->fecha($dia),
            'fecha_vencimiento' => $this->fecha($dia + 15), 'proveedor_cif' => null, 'proveedor_nombre' => null,
            'forma_pago' => 'TRANSFERENCIA', 'estado' => $estado, 'fecha_pago' => $diaPago !== null ? $this->fecha($diaPago) : null,
            'documento' => null, 'persona_id' => $this->admin, 'observaciones' => null,
        ]);

        $base = $this->lineaProveedor($facturaId, $concepto, $cantidad, $precio, 0, 'SI', null, $cursoId);
        $this->totalesProveedor($facturaId, round($base * $irpfPct / 100, 2));
    }

    private function lineaProveedor(int $facturaId, string $concepto, float $cantidad, float $precio, float $iva, string $exento, ?int $articuloId, ?int $cursoId): float
    {
        $base = round($cantidad * $precio, 2);

        DB::table('factura_empresa_proveedora_linea')->insert([
            'factura_empresa_proveedora_id' => $facturaId, 'concepto' => $concepto, 'tipo_coste' => 'DIRECTO',
            'curso_id' => $cursoId, 'articulo_id' => $articuloId, 'cantidad' => $cantidad, 'precio_unitario' => $precio,
            'descuento_pct' => 0, 'tipo_iva' => $iva, 'exento' => $exento,
            'base' => $base, 'cuota_iva' => $exento === 'SI' ? 0 : round($base * $iva / 100, 2),
        ]);

        return $base;
    }

    /** Igual que FacturaEmpresaProveedora::recalcularTotales(): total = base + IVA − retención. */
    private function totalesProveedor(int $facturaId, float $retencion): void
    {
        $lineas = DB::table('factura_empresa_proveedora_linea')->where('factura_empresa_proveedora_id', $facturaId);
        $base = round((float) (clone $lineas)->sum('base'), 2);
        $iva  = round((float) (clone $lineas)->sum('cuota_iva'), 2);

        DB::table('factura_empresa_proveedora')->where('id', $facturaId)->update([
            'base_imponible' => $base, 'total_iva' => $iva, 'retencion_irpf' => $retencion,
            'total' => round($base + $iva - $retencion, 2),
        ]);
    }

    /* =====================================================================
     *  Peticiones de logística
     * ===================================================================== */

    /**
     * Crea una petición PENDIENTE. Producto del catálogo: [codigo, cantidad]; producto libre: ['producto' => …, 'cantidad' => …].
     * Si es de un curso, toma de él el centro y las fechas (como completarDesdeCurso de la app).
     */
    private function peticion(?object $curso, int $almacen, int $dia, array $productos, ?string $obs, array $extra = []): int
    {
        $peticionId = $this->insertar('peticiones_logistica', $extra + [
            'curso_id'                  => $curso?->id,
            'solicitante_id'            => $this->coordinador,
            'almacen_id'                => $almacen,
            'almacen_destino_id'        => null,
            'centro_id'                 => $curso?->centro_id,
            'fecha'                     => $this->fecha($dia),
            'fecha_necesaria'           => $curso?->fecha_inicio,
            'fecha_devolucion_prevista' => $curso?->fecha_fin,
            'estado'                    => 'PENDIENTE',
            'observaciones'             => $obs,
            'direccion_envio'           => null,
            'transportista'             => null,
            'seguimiento'               => null,
            'coste_envio'               => 0,
        ]);

        foreach ($productos as $p) {
            $libre = isset($p['producto']);
            DB::table('peticion_producto')->insert([
                'articulo_id'             => $libre ? null : $this->articuloId($p[0]),
                'producto'                => $libre ? $p['producto'] : AlmacenSeeder::ARTICULOS[$p[0]][0],
                'cantidad'                => $libre ? $p['cantidad'] : $p[1],
                'cantidad_servida'        => 0,
                'cantidad_devuelta'       => 0,
                'peticiones_logistica_id' => $peticionId,
            ]);
        }

        return $peticionId;
    }

    /**
     * Sirve la petición (SALIDA_CURSO, o TRASPASO si tiene almacén de destino), como servirPeticion de la app.
     * $parcial: codigo => cantidad a servir; si no se indica, se sirve todo lo pendiente.
     */
    private function servir(int $peticionId, int $dia, array $envio = [], array $parcial = []): void
    {
        $p = DB::table('peticiones_logistica')->find($peticionId);
        $traspaso = $p->almacen_destino_id !== null;

        $lineas = [];
        $servido = [];
        foreach ($this->productos($peticionId) as $prod) {
            if (! $prod->codigo) {
                continue;   // producto libre, sin artículo del catálogo: no se puede servir desde el almacén
            }
            $cantidad = $parcial ? ($parcial[$prod->codigo] ?? 0) : $prod->cantidad - $prod->cantidad_servida;
            if ($cantidad <= 0) {
                continue;
            }

            if ($prod->controla_serie === 'SI') {
                foreach ($this->unidadesEn($prod->codigo, $p->almacen_id, $cantidad) as $ns) {
                    $lineas[] = ['articulo' => $prod->codigo, 'cantidad' => 1, 'serie' => $ns];
                }
            } else {
                $lineas[] = ['articulo' => $prod->codigo, 'cantidad' => $cantidad];
            }
            $servido[$prod->id] = $cantidad;
        }

        $this->movimiento($traspaso ? 'TRASPASO' : 'SALIDA_CURSO', $dia, [
            'origen' => $p->almacen_id, 'destino' => $p->almacen_destino_id,
            'curso' => $traspaso ? null : $p->curso_id, 'peticion' => $peticionId,
            'referencia' => $envio['seguimiento'] ?? null,
        ], $lineas);

        foreach ($servido as $id => $cantidad) {
            DB::table('peticion_producto')->where('id', $id)->increment('cantidad_servida', $cantidad);
        }

        $pendiente = DB::table('peticion_producto')->where('peticiones_logistica_id', $peticionId)
            ->whereColumn('cantidad_servida', '<', 'cantidad')->exists();

        DB::table('peticiones_logistica')->where('id', $peticionId)->update($envio + [
            'estado' => $pendiente ? 'EN_PROCESO' : ($traspaso ? 'COMPLETADA' : 'ENTREGADA'),
        ]);
    }

    /** Devuelve todo el material retornable (DEVOLUCION_CURSO al almacén de la petición), como devolverPeticion. */
    private function devolver(int $peticionId, int $dia): void
    {
        $p = DB::table('peticiones_logistica')->find($peticionId);

        $lineas = [];
        foreach ($this->productos($peticionId) as $prod) {
            $cantidad = $prod->retornable === 'SI' ? $prod->cantidad_servida - $prod->cantidad_devuelta : 0;
            if ($cantidad <= 0) {
                continue;
            }

            if ($prod->controla_serie === 'SI') {
                $unidades = DB::table('articulo_serie')->where('articulo_id', $prod->articulo_id)
                    ->where('curso_id', $p->curso_id)->where('estado', 'PRESTADO')
                    ->orderBy('id')->limit($cantidad)->pluck('numero_serie');
                foreach ($unidades as $ns) {
                    $lineas[] = ['articulo' => $prod->codigo, 'cantidad' => 1, 'serie' => $ns];
                }
            } else {
                $lineas[] = ['articulo' => $prod->codigo, 'cantidad' => $cantidad];
            }
            DB::table('peticion_producto')->where('id', $prod->id)->increment('cantidad_devuelta', $cantidad);
        }

        $this->movimiento('DEVOLUCION_CURSO', $dia, ['destino' => $p->almacen_id, 'curso' => $p->curso_id, 'peticion' => $peticionId], $lineas);
        $this->estado($peticionId, 'DEVUELTA');
    }

    private function estado(int $peticionId, string $estado): void
    {
        DB::table('peticiones_logistica')->where('id', $peticionId)->update(['estado' => $estado]);
    }

    private function productos(int $peticionId)
    {
        return DB::table('peticion_producto as pp')
            ->leftJoin('articulo as a', 'a.id', '=', 'pp.articulo_id')
            ->where('pp.peticiones_logistica_id', $peticionId)
            ->select('pp.*', 'a.codigo', 'a.controla_serie', 'a.retornable')
            ->orderBy('pp.id')
            ->get();
    }

    private function ticket(int $peticionId, int $personaId, int $dia, string $titulo, string $mensaje): void
    {
        $mensajeId = $this->insertar('peticion_mensajes', [
            'mensaje' => $mensaje, 'fecha_creada' => $this->fecha($dia), 'hora_creada' => sprintf('10:%02d:00', (10 + $peticionId * 7) % 60),
            'persona_id' => $personaId,
        ]);

        DB::table('peticion_ticket')->insert([
            'peticion_mensajes_id' => $mensajeId, 'peticiones_logistica_id' => $peticionId, 'titulo' => $titulo,
        ]);
    }

    /* =====================================================================
     *  Movimientos de almacén (mismas reglas que AlmacenService)
     * ===================================================================== */

    /**
     * Líneas: ['articulo' => codigo, 'cantidad' => n] o con nº de serie:
     *   'nueva' => NS (alta de una unidad en una entrada) o 'serie' => NS (unidad existente).
     */
    private function movimiento(string $tipo, int $dia, array $cab, array $lineas): void
    {
        if (! $lineas) {
            throw new RuntimeException("Movimiento $tipo sin líneas");
        }

        $origen  = $cab['origen'] ?? null;
        $destino = $cab['destino'] ?? null;
        $fecha   = $this->fecha($dia);

        $movId = $this->insertar('movimiento_almacen', [
            'tipo'                          => $tipo,
            'fecha'                         => $fecha . ' ' . sprintf('%02d:%02d:00', 9 + abs($dia) % 8, (abs($dia) * 7) % 60),
            'almacen_origen_id'             => $origen,
            'almacen_destino_id'            => $destino,
            'curso_id'                      => $cab['curso'] ?? null,
            'peticiones_logistica_id'       => $cab['peticion'] ?? null,
            'proveedor_id'                  => $cab['proveedor'] ?? null,
            'factura_empresa_proveedora_id' => $cab['factura'] ?? null,
            'persona_id'                    => $this->logista,
            'referencia'                    => $cab['referencia'] ?? null,
            'observaciones'                 => $cab['observaciones'] ?? null,
        ]);

        foreach ($lineas as $l) {
            $articulo = DB::table('articulo')->where('codigo', $l['articulo'])->first();
            $serieId = null;

            if (isset($l['nueva'])) {
                $serieId = $this->insertar('articulo_serie', [
                    'articulo_id' => $articulo->id, 'numero_serie' => $l['nueva'], 'almacen_id' => $destino,
                    'curso_id' => null, 'estado' => 'DISPONIBLE', 'fecha_alta' => $fecha,
                ]);
            } elseif (isset($l['serie'])) {
                $serieId = $this->moverUnidad($tipo, $articulo->id, $l['serie'], $origen, $destino, $cab['curso'] ?? null);
            }

            if ($origen) {
                $this->sumarStock($origen, $articulo, -$l['cantidad']);
            }
            if ($destino) {
                $this->sumarStock($destino, $articulo, $l['cantidad']);
            }

            DB::table('movimiento_almacen_linea')->insert([
                'movimiento_almacen_id' => $movId,
                'articulo_id'           => $articulo->id,
                'articulo_serie_id'     => $serieId,
                'cantidad'              => $l['cantidad'],
                'coste_unitario'        => $l['coste'] ?? $articulo->precio_coste,
            ]);
        }
    }

    private function moverUnidad(string $tipo, int $articuloId, string $ns, ?int $origen, ?int $destino, ?int $cursoId): int
    {
        $u = DB::table('articulo_serie')->where('articulo_id', $articuloId)->where('numero_serie', $ns)->first();

        if ($origen && $u->almacen_id != $origen) {
            throw new RuntimeException("La unidad $ns no está en el almacén de origen");
        }

        $cambios = match ($tipo) {
            'SALIDA_CURSO'     => ['almacen_id' => null, 'curso_id' => $cursoId, 'estado' => 'PRESTADO'],
            'DEVOLUCION_CURSO' => ['almacen_id' => $destino, 'curso_id' => null, 'estado' => 'DISPONIBLE'],
            'TRASPASO'         => ['almacen_id' => $destino],
            'BAJA'             => ['almacen_id' => null, 'curso_id' => null, 'estado' => 'BAJA'],
            default            => throw new RuntimeException("Tipo $tipo no previsto para unidades con nº de serie"),
        };

        DB::table('articulo_serie')->where('id', $u->id)->update($cambios);

        return $u->id;
    }

    private function sumarStock(int $almacenId, object $articulo, int $cantidad): void
    {
        $actual = DB::table('stock')->where('almacen_id', $almacenId)->where('articulo_id', $articulo->id)->value('cantidad');

        if ($actual === null) {
            if ($cantidad < 0) {
                throw new RuntimeException("Sin stock de {$articulo->codigo} en el almacén $almacenId");
            }
            DB::table('stock')->insert(['almacen_id' => $almacenId, 'articulo_id' => $articulo->id, 'cantidad' => $cantidad]);

            return;
        }

        if ($actual + $cantidad < 0) {
            throw new RuntimeException("No hay stock suficiente de {$articulo->codigo}: hay $actual y se piden " . -$cantidad);
        }

        DB::table('stock')->where('almacen_id', $almacenId)->where('articulo_id', $articulo->id)
            ->update(['cantidad' => $actual + $cantidad]);
    }

    /* =====================================================================
     *  Ayudas
     * ===================================================================== */

    /** Unidades DISPONIBLES de un artículo en un almacén (las primeras por id, como hace la app). */
    private function unidadesEn(string $codigo, int $almacen, int $cuantas): array
    {
        $ns = DB::table('articulo_serie')->where('articulo_id', $this->articuloId($codigo))
            ->where('almacen_id', $almacen)->where('estado', 'DISPONIBLE')
            ->orderBy('id')->limit($cuantas)->pluck('numero_serie')->all();

        if (count($ns) !== $cuantas) {
            throw new RuntimeException("Faltan unidades de $codigo en el almacén $almacen: hay " . count($ns) . ", se piden $cuantas");
        }

        return $ns;
    }

    private function series(string $prefijo, int $desde, int $hasta): array
    {
        return array_map(fn ($n) => sprintf('%s-%s-%04d', $prefijo, $this->anio(-150), $n), range($desde, $hasta));
    }

    private function curso(int $accion, int $grupo): object
    {
        return DB::table('curso')->where('id', $this->cursoId($accion, $grupo))->first();
    }

    /** Días desde hoy de una fecha concreta + desplazamiento (para "dos días antes del inicio del curso"). */
    private function desde(string $fecha, int $desplazamiento): int
    {
        return (int) $this->hoy()->diffInDays(\Illuminate\Support\Carbon::parse($fecha), false) + $desplazamiento;
    }

    private function anio(int $dia): int
    {
        return $this->hoy()->addDays($dia)->year;
    }
}
