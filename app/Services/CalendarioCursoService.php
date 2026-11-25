<?php

namespace App\Services;

use App\Models\Curso;
use App\Models\CursoCalendario;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;


class CalendarioCursoService
{
    // Campos del curso que afectan a los días ya creados
    public const CAMPOS = ['fecha_inicio', 'fecha_fin', 'hora_inicio', 'hora_final'];

    // Máximo de días que se generan de una vez (evita bucles enormes con fechas erróneas)
    private const MAX_DIAS = 366;

    /** Fechas (AAAA-MM-DD) del periodo del curso que encajan con el patrón. */
    public function fechas(Curso $curso, array $patron): array
    {
        if (! $curso->fecha_inicio || ! $curso->fecha_fin) {
            return [];
        }

        $inicio = Carbon::parse($curso->fecha_inicio)->startOfDay();
        $fin    = Carbon::parse($curso->fecha_fin)->startOfDay();
        if ($fin->lt($inicio) || $inicio->diffInDays($fin) > self::MAX_DIAS) {
            return [];
        }

        $modo = $patron['modo'] ?? 'semana';
        $dias = array_map('intval', (array) ($patron['dias'] ?? []));

        $fechas = [];
        foreach (CarbonPeriod::create($inicio, $fin) as $i => $dia) {
            $vale = $modo === 'alterno'
                ? $i % 2 === 0                            // día 0, 2, 4... desde el inicio
                : in_array($dia->isoWeekday(), $dias, true);

            if ($vale) {
                $fechas[] = $dia->toDateString();
            }
        }

        return $fechas;
    }

    /**
     * Añade los días del patrón que todavía no estén en el calendario, con el horario del curso
     * y, si se indica, un segundo tramo. No modifica los días que ya existen.
     */
    public function generar(Curso $curso, array $patron, ?string $inicio2 = null, ?string $fin2 = null): int
    {
        if (! $curso->hora_inicio || ! $curso->hora_final) {
            return 0;
        }

        $segundo = $inicio2 && $fin2;
        $existentes = $curso->calendario()->pluck('fecha')
            ->map(fn ($f) => Carbon::parse($f)->toDateString())
            ->all();

        $nuevos = [];
        foreach ($this->fechas($curso, $patron) as $fecha) {
            if (in_array($fecha, $existentes, true)) {
                continue;
            }
            $nuevos[] = [
                'curso_id'     => $curso->id,
                'fecha'        => $fecha,
                'hora_inicio1' => $this->hora($curso->hora_inicio),
                'hora_fin1'    => $this->hora($curso->hora_final),
                'hora_inicio2' => $segundo ? $this->hora($inicio2) : null,
                'hora_fin2'    => $segundo ? $this->hora($fin2) : null,
            ];
        }

        CursoCalendario::insert($nuevos);

        return count($nuevos);
    }

    /**
     * Ajusta los días existentes después de editar el curso:
     *  - quita los días que se quedan fuera de las nuevas fechas;
     *  - si cambia el horario, actualiza el primer tramo de los días que tenían el horario
     *    anterior (los días con horas cambiadas a mano no se tocan).
     *
     * @param  array  $antes  Valores de self::CAMPOS antes de guardar
     * @return array{quitados:int, actualizados:int}
     */
    public function ajustar(Curso $curso, array $antes): array
    {
        $resultado = ['quitados' => 0, 'actualizados' => 0];

        $inicio = $curso->fecha_inicio ? Carbon::parse($curso->fecha_inicio)->toDateString() : null;
        $fin    = $curso->fecha_fin ? Carbon::parse($curso->fecha_fin)->toDateString() : null;

        $horasAntes = [$this->hora($antes['hora_inicio'] ?? null), $this->hora($antes['hora_final'] ?? null)];
        $horasAhora = [$this->hora($curso->hora_inicio), $this->hora($curso->hora_final)];
        $cambiaHorario = $horasAntes[0] && $horasAntes[1] && $horasAhora[0] && $horasAhora[1] && $horasAntes !== $horasAhora;

        foreach ($curso->calendario()->get() as $dia) {
            $fecha = $dia->fecha->toDateString();

            if (($inicio && $fecha < $inicio) || ($fin && $fecha > $fin)) {
                $dia->delete();
                $resultado['quitados']++;
                continue;
            }

            if ($cambiaHorario && [$this->hora($dia->hora_inicio1), $this->hora($dia->hora_fin1)] === $horasAntes) {
                // Si el nuevo primer tramo se mete en el segundo, el día se deja como estaba
                if ($dia->hora_inicio2 && $horasAhora[1] > $this->hora($dia->hora_inicio2)) {
                    continue;
                }
                $dia->update(['hora_inicio1' => $horasAhora[0], 'hora_fin1' => $horasAhora[1]]);
                $resultado['actualizados']++;
            }
        }

        return $resultado;
    }

    private function hora($h): ?string
    {
        return $h ? substr((string) $h, 0, 5) : null;
    }
}
