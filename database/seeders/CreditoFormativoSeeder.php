<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\AyudasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crédito formativo FUNDAE de cada empresa cliente y año.
 *
 * El consumido es exactamente lo imputado en sus cursos de ese año (suma de costes,
 * igual que calcula CreditoFormativoController), así la pantalla de crédito no muestra avisos.
 * El total se elige para que haya créditos en todos los estados: en uso, casi agotado, agotado y sin usar.
 */
class CreditoFormativoSeeder extends Seeder
{
    use AyudasSeeder;

    // empresa => total del crédito como múltiplo de lo consumido (null = importe fijo si no hay cursos)
    private const FACTOR = [
        'TRANS' => 2.20,   // En uso
        'DENT'  => 3.00,   // En uso, holgado
        'HOTEL' => 1.10,   // Casi agotado (≥ 80 %)
        'CONS'  => 1.60,   // En uso
        'SUPER' => 1.00,   // Agotado
        'TALL'  => 1.30,   // Casi agotado
        'ASES'  => 2.00,   // En uso
        // PAN no tiene crédito del año actual: sale en "Empresas sin crédito" del panel del administrador
    ];

    // Crédito mínimo por trabajador, para las empresas sin cursos ese año (estado "Sin usar")
    private const EUROS_POR_TRABAJADOR = 65;

    public function run(): void
    {
        $anioActual = $this->hoy()->year;

        // Años con cursos + el actual y el anterior
        $anios = DB::table('curso')->selectRaw('DISTINCT ' . $this->anioSql('fecha_inicio') . ' AS anio')->pluck('anio')
            ->map(fn ($a) => (int) $a)
            ->merge([$anioActual - 1, $anioActual])
            ->unique()->sort()->values();

        foreach (self::FACTOR as $clave => $factor) {
            $empresaId = $this->empresaId($clave);
            $trabajadores = EmpresaSeeder::EMPRESAS[$clave]['trabajadores'];

            foreach ($anios as $anio) {
                $consumido = round((float) DB::table('coste')
                    ->join('curso', 'curso.id', '=', 'coste.curso_id')
                    ->where('coste.empresa_id', $empresaId)
                    ->whereRaw($this->anioSql('curso.fecha_inicio') . ' = ?', [$anio])
                    ->sum(DB::raw('coste.directos + coste.indirectos + coste.organizacion + coste.salariales')), 2);

                $total = $consumido > 0
                    ? round($consumido * $factor, 2)
                    : round($trabajadores * self::EUROS_POR_TRABAJADOR, 2);

                DB::table('credito_formativo')->insert([
                    'año'               => $anio,
                    'importe_total'     => $total,
                    'importe_consumido' => $consumido,
                    'empresa_id'        => $empresaId,
                ]);
            }
        }
    }

    /** YEAR() en MySQL/MariaDB; strftime en SQLite (tests). */
    private function anioSql(string $columna): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', $columna) AS INTEGER)"
            : "YEAR($columna)";
    }
}
