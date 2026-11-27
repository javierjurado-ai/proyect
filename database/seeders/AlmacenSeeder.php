<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\AyudasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Almacenes y catálogo de artículos. Se crean SIN stock:
 * el stock sale de las compras y movimientos de LogisticaSeeder, como en la aplicación.
 */
class AlmacenSeeder extends Seeder
{
    use AyudasSeeder;

    public const MADRID = 'Almacén central Madrid';
    public const GETAFE = 'Almacén Getafe';

    // codigo => [nombre, tipo, unidad, retornable, controla_serie, precio_coste, precio_venta, stock_minimo, proveedor, activo, descripcion]
    public const ARTICULOS = [
        'EQ-PORT'   => ['Portátil 14" i5 16 GB', 'EQUIPO', 'UNIDAD', 'SI', 'SI', 549.00, null, 10, 'INFO', true, 'Portátil para alumnos con Windows 11 y Office'],
        'EQ-PROY'   => ['Proyector Full HD', 'EQUIPO', 'UNIDAD', 'SI', 'SI', 389.00, null, 3, 'INFO', true, null],
        'EQ-TABLET' => ['Tablet 10" con funda', 'EQUIPO', 'UNIDAD', 'SI', 'SI', 229.00, null, 4, 'INFO', true, null],
        'EQ-RATON'  => ['Ratón inalámbrico', 'EQUIPO', 'UNIDAD', 'SI', 'NO', 9.90, null, 10, 'INFO', true, null],
        'EQ-RETRO'  => ['Retroproyector', 'EQUIPO', 'UNIDAD', 'SI', 'NO', 120.00, null, 0, 'INFO', false, 'Descatalogado'],
        'FU-CUAD'   => ['Cuaderno A4 cuadriculado', 'FUNGIBLE', 'UNIDAD', 'NO', 'NO', 1.20, 2.00, 50, 'PAPEL', true, null],
        'FU-BOLI'   => ['Bolígrafo azul (caja de 50)', 'FUNGIBLE', 'CAJA', 'NO', 'NO', 6.50, 10.00, 4, 'PAPEL', true, null],
        'MD-PRL'    => ['Manual de PRL nivel básico', 'MATERIAL_DIDACTICO', 'UNIDAD', 'NO', 'NO', 6.00, 9.00, 20, 'PAPEL', true, null],
        'MD-MANIP'  => ['Manual de manipulador de alimentos', 'MATERIAL_DIDACTICO', 'UNIDAD', 'NO', 'NO', 4.50, 7.00, 20, 'PAPEL', true, null],
        'MD-AUX'    => ['Manual de primeros auxilios', 'MATERIAL_DIDACTICO', 'UNIDAD', 'NO', 'NO', 5.20, 8.00, 15, 'PAPEL', true, null],
        'OT-EPI'    => ['Kit EPI (casco, chaleco y gafas)', 'OTRO', 'UNIDAD', 'SI', 'NO', 18.50, null, 10, 'EQUIP', true, null],
        'OT-LIC'    => ['Licencia Microsoft 365 (3 meses)', 'OTRO', 'LICENCIA', 'NO', 'NO', 8.00, null, 10, 'INFO', true, 'Licencia temporal para alumnos de teleformación'],
        'MO-PIZ'    => ['Pizarra blanca con trípode', 'MOBILIARIO', 'UNIDAD', 'SI', 'NO', 79.00, null, 0, 'EQUIP', true, null],
    ];

    public function run(): void
    {
        $logista = $this->personaId(PersonaSeeder::PERSONAL['logista'][5]);

        DB::table('almacen')->insert([
            ['nombre' => self::MADRID, 'direccion' => 'Calle de Bravo Murillo 145, sótano', 'municipio' => 'Madrid', 'provincia' => 'Madrid',
                'centro_id' => $this->idDe('centro', ['nombre' => CentroSeeder::CENTROS['MAD']['nombre']]), 'responsable_id' => $logista, 'activo' => 1],
            ['nombre' => self::GETAFE, 'direccion' => 'Avenida de John Lennon 22', 'municipio' => 'Getafe', 'provincia' => 'Madrid',
                'centro_id' => $this->idDe('centro', ['nombre' => CentroSeeder::CENTROS['GET']['nombre']]), 'responsable_id' => null, 'activo' => 1],
        ]);

        foreach (self::ARTICULOS as $codigo => [$nombre, $tipo, $unidad, $retornable, $serie, $coste, $venta, $minimo, $proveedor, $activo, $desc]) {
            DB::table('articulo')->insert([
                'codigo'                => $codigo,
                'nombre'                => $nombre,
                'descripcion'           => $desc,
                'tipo'                  => $tipo,
                'unidad'                => $unidad,
                'retornable'            => $retornable,
                'controla_serie'        => $serie,
                'precio_coste'          => $coste,
                'precio_venta'          => $venta,
                'tipo_iva'              => 21.00,
                'stock_minimo'          => $minimo,
                'proveedor_habitual_id' => $this->empresaId($proveedor),
                'activo'                => $activo ? 1 : 0,
            ]);
        }
    }
}
