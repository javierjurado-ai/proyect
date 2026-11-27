<?php

namespace Database\Seeders\Concerns;

use Database\Seeders\EmpresaSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Utilidades compartidas por los seeders de datos de prueba.
 *
 * Las fechas son RELATIVAS al día en que se ejecuta el seeder (hoy ± N días),
 * así siempre hay cursos en marcha, cursos que empiezan esta semana, plazos que vencen, etc.
 */
trait AyudasSeeder
{
    protected function hoy(): Carbon
    {
        return Carbon::today();
    }

    /** Fecha AAAA-MM-DD a $dias de hoy (negativo = pasado). */
    protected function fecha(int $dias): string
    {
        return $this->hoy()->addDays($dias)->toDateString();
    }

    /** Inserta una fila y devuelve su id. */
    protected function insertar(string $tabla, array $fila): int
    {
        return (int) DB::table($tabla)->insertGetId($fila);
    }

    /** Id de una fila que tiene que existir (si no, el seeder falla con un mensaje claro). */
    protected function idDe(string $tabla, array $donde): int
    {
        $id = DB::table($tabla)->where($donde)->value('id');

        if (! $id) {
            throw new RuntimeException("No existe en $tabla: " . json_encode($donde, JSON_UNESCAPED_UNICODE));
        }

        return (int) $id;
    }

    protected function empresaId(string $clave): int
    {
        return $this->idDe('empresa', ['cif' => EmpresaSeeder::cif($clave)]);
    }

    protected function personaId(string $email): int
    {
        return $this->idDe('persona', ['email' => $email]);
    }

    protected function rolId(string $rol): int
    {
        return $this->idDe('rol', ['rol' => $rol]);
    }

    protected function cursoId(int $accion, int $grupo): int
    {
        return $this->idDe('curso', ['codigo_accion' => (string) $accion, 'codigo_grupo' => (string) $grupo]);
    }

    protected function articuloId(string $codigo): int
    {
        return $this->idDe('articulo', ['codigo' => $codigo]);
    }

    protected function almacenId(string $nombre): int
    {
        return $this->idDe('almacen', ['nombre' => $nombre]);
    }

    // Festivos nacionales de fecha fija (MM-DD) y Nochebuena/Nochevieja: no hay clase
    private const FESTIVOS = ['01-01', '01-06', '05-01', '08-15', '10-12', '11-01', '12-06', '12-08', '12-24', '12-25', '12-31'];

    /**
     * Los $n primeros días de clase a partir de $desde (incluido) que caen en $diasSemana
     * (ISO: 1 = lunes … 7 = domingo), saltando los festivos.
     *
     * @return Carbon[]
     */
    protected function diasClase(Carbon $desde, array $diasSemana, int $n): array
    {
        $dias = [];
        for ($d = $desde->copy(); count($dias) < $n; $d->addDay()) {
            if (in_array($d->isoWeekday(), $diasSemana, true) && ! in_array($d->format('m-d'), self::FESTIVOS, true)) {
                $dias[] = $d->copy();
            }
        }

        return $dias;
    }

    /** Horas entre dos horas "HH:MM". */
    protected function horasEntre(string $inicio, string $fin): float
    {
        [$h1, $m1] = array_map('intval', explode(':', $inicio));
        [$h2, $m2] = array_map('intval', explode(':', $fin));

        return (($h2 * 60 + $m2) - ($h1 * 60 + $m1)) / 60;
    }

    /* ------------------------------------------------------------------
     *  Identificadores con su dígito de control correcto (pasan validaciones)
     * ------------------------------------------------------------------ */

    protected function dni(int $numero): string
    {
        return sprintf('%08d', $numero) . 'TRWAGMYFPDXBNJZSQVHLCKE'[$numero % 23];
    }

    /** NIE: X/Y/Z + 7 cifras + letra. */
    protected function nie(string $prefijo, int $numero): string
    {
        $valor = (int) (strpos('XYZ', $prefijo) . sprintf('%07d', $numero));

        return $prefijo . sprintf('%07d', $numero) . 'TRWAGMYFPDXBNJZSQVHLCKE'[$valor % 23];
    }

    /** IBAN español con los dígitos de control correctos a partir de una cuenta de 20 cifras. */
    protected function iban(string $cuenta20): string
    {
        $resto = 0;
        foreach (str_split($cuenta20 . '142800') as $cifra) {   // E=14, S=28, 00
            $resto = ($resto * 10 + (int) $cifra) % 97;
        }

        return 'ES' . sprintf('%02d', 98 - $resto) . $cuenta20;
    }
}
