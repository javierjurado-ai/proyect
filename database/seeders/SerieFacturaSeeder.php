<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Series de facturación F (ordinarias) y R (rectificativas) del año pasado y del actual.
 * El último número lo actualiza FacturacionSeeder al emitir.
 */
class SerieFacturaSeeder extends Seeder
{
    public function run(): void
    {
        $anio = Carbon::today()->year;

        foreach ([$anio - 1, $anio] as $a) {
            DB::table('serie_factura')->insert([
                ['codigo' => 'F', 'descripcion' => 'Facturas ordinarias', 'anio' => $a, 'ultimo_numero' => 0],
                ['codigo' => 'R', 'descripcion' => 'Facturas rectificativas', 'anio' => $a, 'ultimo_numero' => 0],
            ]);
        }
    }
}
