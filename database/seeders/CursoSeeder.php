<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\AyudasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Cursos y todo lo que cuelga de ellos: presupuesto, calendario, aula virtual, coordinador,
 * empresas participantes, alumnos, docentes, costes, informes RLT y comunicaciones FUNDAE.
 *
 * Las fechas son relativas a hoy: hay cursos terminados, en marcha, que empiezan esta semana y futuros.
 * Las horas del curso salen de su calendario (días × horas al día), así todo cuadra.
 */
class CursoSeeder extends Seeder
{
    use AyudasSeeder;

    // Módulos económicos (€ por alumno y hora) para calcular el coste bonificable
    private const MODULO_PRESENCIAL    = 13.00;
    private const MODULO_TELEFORMACION = 7.50;
    private const COSTE_SALARIAL_HORA  = 14.50;

    private const TEC_MADRID   = 'Elena Ruiz (Comunidad de Madrid)';
    private const TEC_VALENCIA = 'Pau Climent (Generalitat Valenciana)';
    private const TEC_SEVILLA  = 'Manuel Ortiz (Junta de Andalucía)';
    private const TEC_TOLEDO   = 'Ana Ballesteros (JCCM)';

    /**
     * Plan de cursos.
     *  inicio: días desde hoy (el curso empieza el primer día de clase a partir de ahí)
     *  dias:   días de la semana con clase (1 = lunes … 7 = domingo)
     *  n:      número de días de clase
     *  h / h2: primer y segundo tramo horario (h2 solo en turno partido)
     *  ht:     horas de teleformación en los cursos MIXTA
     *  empresas: empresa cliente => nº de alumnos
     */
    public const CURSOS = [
        // --- Terminados ---
        ['accion' => 1001, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'PRESENCIAL', 'centro' => 'MAD', 'aula' => 'Aula 1',
            'inicio' => -110, 'dias' => [1, 2, 3, 4], 'n' => 12, 'h' => ['09:00', '14:00'],
            'empresas' => ['TRANS' => 6, 'CONS' => 5, 'TALL' => 3], 'docente' => 'D1', 'tecnico' => self::TEC_MADRID, 'presupuesto' => true],
        ['accion' => 1002, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'PRESENCIAL', 'centro' => 'SEV', 'aula' => 'Salón Giralda',
            'inicio' => -75, 'dias' => [2, 3], 'n' => 2, 'h' => ['09:00', '14:00'],
            'empresas' => ['HOTEL' => 10], 'docente' => 'D3', 'tecnico' => self::TEC_SEVILLA],
        ['accion' => 1003, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'TELEFORMACION', 'centro' => null, 'aula' => 'Online',
            'inicio' => -60, 'dias' => [1, 3], 'n' => 10, 'h' => ['16:00', '19:00'],
            'empresas' => ['ASES' => 4, 'DENT' => 3, 'SUPER' => 5], 'docente' => 'D5', 'tecnico' => self::TEC_MADRID, 'presupuesto' => true],
        ['accion' => 1006, 'grupo' => 1, 'formadora' => 'ITL', 'modalidad' => 'PRESENCIAL', 'centro' => 'GET', 'aula' => 'Patio de maniobras',
            'inicio' => -45, 'dias' => [1, 2, 3, 4], 'n' => 4, 'h' => ['08:00', '13:00'],
            'empresas' => ['SUPER' => 6, 'TRANS' => 4], 'docente' => 'D6', 'tecnico' => self::TEC_MADRID,
            'observaciones' => 'Impartido por Instituto Técnico Levante (subcontratado)'],
        ['accion' => 1007, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'PRESENCIAL', 'centro' => 'VAL', 'aula' => 'Sala de formación',
            'inicio' => -30, 'dias' => [1, 2, 3], 'n' => 3, 'h' => ['09:00', '13:00'],
            'empresas' => ['SUPER' => 12], 'docente' => 'D1', 'tecnico' => self::TEC_VALENCIA],

        // --- En marcha ---
        ['accion' => 1004, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'MIXTA', 'centro' => 'MAD', 'aula' => 'Aula 2',
            'inicio' => -24, 'dias' => [2, 4], 'n' => 16, 'h' => ['09:00', '11:00'], 'ht' => 28,
            'empresas' => ['HOTEL' => 5, 'DENT' => 2], 'docente' => 'D4', 'tecnico' => self::TEC_MADRID, 'presupuesto' => true,
            'observaciones' => 'Presencial martes y jueves; el resto en el campus virtual'],
        ['accion' => 1005, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'TELEFORMACION', 'centro' => null, 'aula' => 'Online',
            'inicio' => -10, 'dias' => [1, 2, 3, 4, 5], 'n' => 20, 'h' => ['17:00', '19:00'],
            'empresas' => ['ASES' => 3, 'TRANS' => 3, 'CONS' => 2], 'docente' => 'D5', 'tecnico' => self::TEC_MADRID],
        ['accion' => 1012, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'PRESENCIAL', 'centro' => 'GET', 'aula' => 'Taller de mecánica',
            'inicio' => -3, 'dias' => [1, 2, 3, 4, 5], 'n' => 7, 'h' => ['09:00', '13:00'], 'h2' => ['15:00', '17:00'],
            'empresas' => ['TALL' => 6], 'docente' => 'D7', 'tecnico' => self::TEC_MADRID, 'nivel' => 1],
        ['accion' => 1008, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'PRESENCIAL', 'centro' => 'VAL', 'aula' => 'Sala de formación',
            'inicio' => -1, 'dias' => [1, 2, 3, 4], 'n' => 5, 'h' => ['16:00', '20:00'],
            'empresas' => ['SUPER' => 10], 'docente' => 'D8', 'tecnico' => self::TEC_VALENCIA],

        // --- Empiezan en los próximos días (salen en "Requiere tu atención" del coordinador) ---
        ['accion' => 1009, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'TELEFORMACION', 'centro' => null, 'aula' => 'Online',
            'inicio' => 1, 'dias' => [1, 3], 'n' => 8, 'h' => ['18:00', '20:00'],
            'empresas' => ['DENT' => 5, 'ASES' => 4], 'docente' => 'D2', 'tecnico' => self::TEC_MADRID],
        ['accion' => 1013, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'PRESENCIAL', 'centro' => 'SEV', 'aula' => 'Salón Giralda',
            'inicio' => 2, 'dias' => [1, 2, 3, 4, 5], 'n' => 2, 'h' => ['10:00', '14:00'],
            'empresas' => ['HOTEL' => 8], 'docente' => null, 'tecnico' => self::TEC_SEVILLA,
            'observaciones' => 'Pendiente de confirmar docente'],
        ['accion' => 1010, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'PRESENCIAL', 'centro' => 'MAD', 'aula' => 'Aula de informática',
            'inicio' => 5, 'dias' => [2, 4], 'n' => 4, 'h' => ['16:00', '19:00'],
            'empresas' => ['TRANS' => 4, 'ASES' => 2], 'docente' => 'D2', 'tecnico' => self::TEC_MADRID],
        ['accion' => 1011, 'grupo' => 1, 'formadora' => 'AULA', 'modalidad' => 'TELEFORMACION', 'centro' => null, 'aula' => 'Online',
            'inicio' => 6, 'dias' => [1, 2, 3, 4, 5], 'n' => 5, 'h' => ['10:00', '12:00'],
            'empresas' => ['PAN' => 5], 'docente' => 'D8', 'tecnico' => self::TEC_TOLEDO, 'sin_alumnos' => true,
            'observaciones' => 'La empresa aún no ha enviado la lista de participantes'],

        // --- Futuros ---
        ['accion' => 1001, 'grupo' => 2, 'formadora' => 'AULA', 'modalidad' => 'PRESENCIAL', 'centro' => 'GET', 'aula' => 'Aula A',
            'inicio' => 20, 'dias' => [1, 2, 3, 4], 'n' => 12, 'h' => ['09:00', '14:00'],
            'empresas' => ['CONS' => 6, 'TALL' => 3], 'docente' => 'D1', 'tecnico' => self::TEC_MADRID, 'presupuesto' => true],
        ['accion' => 1003, 'grupo' => 2, 'formadora' => 'AULA', 'modalidad' => 'MIXTA', 'centro' => 'MAD', 'aula' => 'Aula de informática',
            'inicio' => 35, 'dias' => [2, 4], 'n' => 5, 'h' => ['16:00', '19:00'], 'ht' => 15,
            'empresas' => ['SUPER' => 5, 'HOTEL' => 4], 'docente' => 'D5', 'tecnico' => self::TEC_MADRID, 'presupuesto' => true],
        ['accion' => 1002, 'grupo' => 2, 'formadora' => 'AULA', 'modalidad' => 'PRESENCIAL', 'centro' => null, 'aula' => 'Obrador del cliente (Toledo)',
            'inicio' => 45, 'dias' => [1, 2], 'n' => 2, 'h' => ['08:00', '13:00'],
            'empresas' => ['PAN' => 6], 'docente' => 'D3', 'tecnico' => self::TEC_TOLEDO],
    ];

    private const NIVELES = ['ESO_EGB', 'BACHILLERATO_FP_MEDIO', 'FP_SUPERIOR', 'GRADO_DIPLOMATURA', 'PRIMARIA',
        'LICENCIATURA_MASTER', 'CP_NIVEL_3', 'OTRAS', 'MENOS_PRIMARIA', 'DOCTORADO'];
    private const CATEGORIAS = ['TRABAJADOR_CUALIFICADO', 'TECNICO', 'TRABAJADOR_BAJA_CUALIFICACION', 'MANDO_INTERMEDIO',
        'TRABAJADOR_CUALIFICADO', 'TECNICO', 'DIRECTIVO'];
    private const DIAS_SEMANA = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];

    private int $matricula = 0;   // contador global para variar los datos de los alumnos

    public function run(): void
    {
        $coordinador = $this->personaId(PersonaSeeder::PERSONAL['coordinador'][5]);
        $nombreCoordinador = implode(' ', array_slice(PersonaSeeder::PERSONAL['coordinador'], 0, 3));

        foreach (self::CURSOS as $i => $c) {
            $formacion = FormacionSeeder::FORMACIONES[$c['accion']];
            $dias      = $this->diasClase($this->hoy()->addDays($c['inicio']), $c['dias'], $c['n']);
            $inicio    = $dias[0];
            $fin       = end($dias);

            $horasDia = $this->horasEntre(...$c['h']) + (isset($c['h2']) ? $this->horasEntre(...$c['h2']) : 0);
            $hp = $c['modalidad'] === 'TELEFORMACION' ? null : round($c['n'] * $horasDia, 2);
            $ht = match ($c['modalidad']) {
                'TELEFORMACION' => round($c['n'] * $horasDia, 2),
                'MIXTA'         => (float) $c['ht'],
                default         => null,
            };

            // Costes por empresa (se usan en el presupuesto, en coste y en facturación)
            $costes = [];
            foreach ($c['empresas'] as $clave => $alumnos) {
                $costes[$clave] = $this->calcularCoste($alumnos, (float) $hp, (float) $ht);
            }

            // --- Presupuesto (solo algunos cursos) ---
            $presupuestoId = null;
            if (! empty($c['presupuesto'])) {
                $presupuestoId = $this->insertar('presupuesto', [
                    'numero_alumnos'      => array_sum($c['empresas']),
                    'nombre_formacion'    => mb_substr($formacion[0], 0, 45),
                    'horas_totales'       => (float) $hp + (float) $ht,
                    'ubicacion_formacion' => $this->ubicacion($c),
                    'importe_bonificado'  => round(array_sum(array_map(fn ($k) => $k['directos'] + $k['indirectos'] + $k['organizacion'], $costes)), 2),
                    'fecha_bonificado'    => $inicio->copy()->subDays(20)->toDateString(),
                ]);
            }

            // --- Curso ---
            $centroId = $c['centro'] ? $this->idDe('centro', ['nombre' => CentroSeeder::CENTROS[$c['centro']]['nombre']]) : null;
            $turno = isset($c['h2']) ? 'PARTIDO' : ((int) substr($c['h'][0], 0, 2) < 14 ? 'MAÑANA' : 'TARDE');

            $cursoId = $this->insertar('curso', [
                'codigo_accion'       => (string) $c['accion'],
                'codigo_grupo'        => (string) $c['grupo'],
                'fecha_inicio'        => $inicio->toDateString(),
                'fecha_fin'           => $fin->toDateString(),
                'hora_inicio'         => $c['h'][0] . ':00',
                'hora_final'          => $c['h'][1] . ':00',
                'turno'               => $turno,
                'aula'                => $c['aula'],
                'tecnico_comunidad'   => $c['tecnico'],
                'observaciones'       => $c['observaciones'] ?? null,
                'objetivos'           => $formacion[4],
                'contenido'           => $formacion[5],
                'horas_presenciales'  => $hp,
                'horas_teleformacion' => $ht,
                'modalidad'           => $c['modalidad'],
                'nivel_formacion'     => $c['nivel'] ?? 0,
                'centro_id'           => $centroId,
                'presupuesto_id'      => $presupuestoId,
                'formacion_id'        => $this->idDe('formacion', ['codigo_accion' => $c['accion']]),
                'empresa_id'          => $this->empresaId($c['formadora']),
            ]);

            // --- Calendario ---
            foreach ($dias as $d) {
                DB::table('curso_calendario')->insert([
                    'curso_id'     => $cursoId,
                    'fecha'        => $d->toDateString(),
                    'hora_inicio1' => $c['h'][0] . ':00',
                    'hora_fin1'    => $c['h'][1] . ':00',
                    'hora_inicio2' => isset($c['h2']) ? $c['h2'][0] . ':00' : null,
                    'hora_fin2'    => isset($c['h2']) ? $c['h2'][1] . ':00' : null,
                ]);
            }

            // --- Aula virtual ---
            if ($c['modalidad'] !== 'PRESENCIAL') {
                DB::table('curso_aula_virtual')->insert([
                    'curso_id'                    => $cursoId,
                    'medio'                       => $c['modalidad'] === 'MIXTA' ? 'Moodle + Microsoft Teams' : 'Moodle',
                    'conexion'                    => "https://campus.aulaactiva.test/course/view.php?id={$cursoId}",
                    'contacto'                    => 'Soporte del campus Aula Activa',
                    'telefono'                    => '910456789',
                    'bimodal'                     => $c['modalidad'] === 'MIXTA' ? 'SI' : 'NO',
                    'sin_participantes_en_centro' => $c['modalidad'] === 'TELEFORMACION' ? 'SI' : 'NO',
                    'sin_docentes_en_centro'      => $c['modalidad'] === 'TELEFORMACION' ? 'SI' : 'NO',
                ]);
            }

            // --- Coordinador del curso ---
            DB::table('curso_contacto')->insert([
                'coordinador' => $nombreCoordinador,
                'actividad'   => 1,
                'curso_id'    => $cursoId,
                'persona_id'  => $coordinador,
            ]);

            // --- Empresas participantes, alumnos, costes e informes RLT ---
            $terminado = $fin->lt($this->hoy());
            foreach ($c['empresas'] as $clave => $alumnos) {
                $empresaId = $this->empresaId($clave);

                DB::table('grupo_empresa')->insert(['empresa_id' => $empresaId, 'curso_id' => $cursoId]);

                if (empty($c['sin_alumnos'])) {
                    $this->matricular($cursoId, $empresaId, $clave, $alumnos, $i, $terminado);
                }

                $this->guardarCoste($cursoId, $empresaId, $costes[$clave], $dias);

                // Las empresas grandes informan a la representación legal de los trabajadores (RLT)
                if (in_array($clave, ['TRANS', 'HOTEL', 'SUPER'], true)) {
                    $desfavorable = $c['accion'] === 1006 && $clave === 'SUPER';
                    DB::table('empresa_rlt')->insert([
                        'informe_rlt'   => 'SI',
                        'valor_informe' => $desfavorable ? 'D' : 'F',
                        'fecha_dis'     => $inicio->copy()->subDays(15)->toDateString(),
                        'resuelto_15'   => 'SI',
                        'empresa_id'    => $empresaId,
                        'curso_id'      => $cursoId,
                    ]);
                }
            }

            // --- Docente ---
            if ($c['docente']) {
                $this->asignarDocente($cursoId, $c, $inicio, $fin, (float) $hp, (float) $ht);
            }

            // --- Comunicaciones FUNDAE ---
            $this->comunicacionesFundae($cursoId, $inicio, $fin, $c['accion'] === 1004);

            // --- Aula ocupada mientras dura el curso ---
            if ($centroId && $inicio->lte($this->hoy()) && $fin->gte($this->hoy())) {
                DB::table('aulas')
                    ->where('centro_id', $centroId)
                    ->where('nombre_aula', $c['aula'])
                    ->update(['estado' => 'OCUPADA', 'curso_id' => $cursoId]);
            }
        }

        // Presupuestos enviados que todavía no tienen curso
        DB::table('presupuesto')->insert([
            ['numero_alumnos' => 12, 'nombre_formacion' => 'Ciberseguridad para usuarios', 'horas_totales' => 16,
                'ubicacion_formacion' => 'Teleformación (campus virtual)', 'importe_bonificado' => 1440.00,
                'fecha_bonificado' => $this->fecha(15)],
            ['numero_alumnos' => 8, 'nombre_formacion' => 'Liderazgo y gestión de equipos', 'horas_totales' => 20,
                'ubicacion_formacion' => 'Madrid - Aula Activa Sede Madrid', 'importe_bonificado' => 2080.00,
                'fecha_bonificado' => $this->fecha(40)],
        ]);
    }

    /** Coste bonificable de una empresa en un curso. */
    public function calcularCoste(int $alumnos, float $hp, float $ht): array
    {
        $total = $alumnos * ($hp * self::MODULO_PRESENCIAL + $ht * self::MODULO_TELEFORMACION);

        return [
            'directos'     => round($total * 0.75, 2),
            'indirectos'   => round($total * 0.10, 2),
            'organizacion' => round($total * 0.15, 2),
            'salariales'   => round($alumnos * $hp * self::COSTE_SALARIAL_HORA, 2),
        ];
    }

    private function ubicacion(array $c): string
    {
        if (! $c['centro']) {
            return $c['modalidad'] === 'TELEFORMACION' ? 'Teleformación (campus virtual)' : $c['aula'];
        }
        $centro = CentroSeeder::CENTROS[$c['centro']];

        return $centro['municipio'] . ' - ' . $centro['nombre'];
    }

    private function matricular(int $cursoId, int $empresaId, string $clave, int $cuantos, int $indiceCurso, bool $terminado): void
    {
        $plantilla = PersonaSeeder::alumnosDe($clave);
        $desde = ($indiceCurso * 3) % count($plantilla);   // se rota para que no sean siempre los mismos

        for ($k = 0; $k < $cuantos; $k++) {
            $m = $this->matricula++;

            DB::table('curso_alumno')->insert([
                'jornada_laboral'                       => $m % 9 === 4 ? 'NO' : 'SI',
                'certificado_afectado_terrorismo'       => 'NO',
                'certificado_afectado_violencia_genero' => $m % 37 === 11 ? 'SI' : 'NO',
                'certificado_discapacidad'              => $m % 23 === 7 ? 'SI' : 'NO',
                'nivel_estudio'                         => self::NIVELES[$m % count(self::NIVELES)],
                'categoria_profesional'                 => self::CATEGORIAS[$m % count(self::CATEGORIAS)],
                'erte'                                  => $m % 29 === 3 ? 'SI' : 'NO',
                'fijo_discontinuo'                      => $clave === 'HOTEL' && $m % 3 === 0 ? 'SI' : 'NO',
                'diploma'                               => $terminado ? 'SI' : 'NO',
                'curso_id'                              => $cursoId,
                'persona_id'                            => $plantilla[($desde + $k) % count($plantilla)],
                'empresa_id'                            => $empresaId,
            ]);
        }
    }

    /** Coste de la empresa en el curso y su reparto por meses según los días de clase. */
    private function guardarCoste(int $cursoId, int $empresaId, array $coste, array $dias): void
    {
        $costeId = $this->insertar('coste', $coste + ['curso_id' => $cursoId, 'empresa_id' => $empresaId]);

        $porMes = [];
        foreach ($dias as $d) {
            $porMes[$d->month] = ($porMes[$d->month] ?? 0) + 1;
        }

        $total = array_sum($coste);
        $repartido = 0;
        $meses = array_keys($porMes);
        foreach ($meses as $j => $mes) {
            $importe = $j === count($meses) - 1
                ? round($total - $repartido, 2)                       // el último mes se lleva el redondeo
                : round($total * $porMes[$mes] / count($dias), 2);
            $repartido += $importe;

            DB::table('curso_coste_periodo')->insert(['mes' => $mes, 'importe' => $importe, 'coste_id' => $costeId]);
        }
    }

    private function asignarDocente(int $cursoId, array $c, Carbon $inicio, Carbon $fin, float $hp, float $ht): void
    {
        $d = PersonaSeeder::DOCENTES[$c['docente']];
        $num = (int) substr($c['docente'], 1);

        $cursoDocenteId = $this->insertar('curso_docente', [
            'numero_horas'       => $hp + $ht,
            'persona_id'         => $this->personaId($d[5]),
            'empresa_id'         => $this->empresaId($d['empresa']),
            'curso_id'           => $cursoId,
            'catagoria'          => $d['categoria'],
            'tipo_contrato'      => $d['contrato'],
            'convenio'           => 'FORMACION NO REGLADA',
            'categoria'          => 'DOCENTE',
            'IBAN'               => $this->iban(sprintf('2100%04d%02d%010d', 1000 + $num * 37, 10 + $num, 1234567890 + $num * 97)),
            'fecha_inicio'       => $inicio->toDateString(),
            'fecha_fin'          => $fin->toDateString(),
            'horas_presenciales' => $hp ?: null,
            'horas_virtuales'    => $ht ?: null,
        ]);

        // OJO: en la BD docente_calendario.turno admite MIXTO, pero el modelo DocenteCalendario lo convierte
        // con el enum Turno (MAÑANA/TARDE/PARTIDO) y 'MIXTO' rompe al leerlo. Mientras no se unifique,
        // el turno partido se guarda como el de su primer tramo.
        $turno = (int) substr($c['h'][0], 0, 2) < 14 ? 'MAÑANA' : 'TARDE';
        foreach ($c['dias'] as $dia) {
            DB::table('docente_calendario')->insert([
                'turno'            => $turno,
                'hora_inicio'      => $c['h'][0] . ':00',
                'hora_fin'         => (isset($c['h2']) ? $c['h2'][1] : $c['h'][1]) . ':00',
                'dia_semana'       => self::DIAS_SEMANA[$dia],
                'curso_docente_id' => $cursoDocenteId,
            ]);
        }

        if ($c['modalidad'] !== 'PRESENCIAL') {
            $tutorias = [['CORREO_ELECTRONICO', 'Respuesta en menos de 48 horas'], ['FORO', 'Foro del curso en el campus']];
            if ($c['modalidad'] === 'MIXTA') {
                $tutorias[] = ['VIDEOCONFERENCIA', 'Tutoría semanal por Teams'];
            }
            foreach ($tutorias as [$tipo, $descripcion]) {
                DB::table('docente_tutoria')->insert([
                    'tipo_tutoria' => $tipo, 'descripcion' => $descripcion, 'curso_docente_id' => $cursoDocenteId,
                ]);
            }
        }

        DB::table('tipo_retribucion')->insert([
            'tipo'             => 'PRECIO HORA',
            'seguridad_social' => $d['categoria'] === 'AUTONOMO' ? 0 : 32.5,
            'importe'          => $d['precio_hora'],
            'fecha'            => $inicio->toDateString(),
            'curso_docente_id' => $cursoDocenteId,
        ]);
    }

    /**
     * Solo se guardan las comunicaciones YA ENVIADAS: en la BD fecha_envio es obligatoria,
     * así que una comunicación pendiente no se puede representar todavía.
     */
    private function comunicacionesFundae(int $cursoId, Carbon $inicio, Carbon $fin, bool $conModificacion): void
    {
        $hoy = $this->hoy();

        $envioInicio = $inicio->copy()->subDays(4);
        if ($envioInicio->lte($hoy)) {
            DB::table('comunicacion_fundae')->insert([
                'tipo' => 'INICIO', 'curso_id' => $cursoId,
                'fecha_limite' => $inicio->copy()->subDays(2)->toDateString(),
                'fecha_envio'  => $envioInicio->toDateString(),
            ]);
        }

        if ($conModificacion && $inicio->copy()->addDays(7)->lte($hoy)) {
            DB::table('comunicacion_fundae')->insert([
                'tipo' => 'MODIFICACION', 'curso_id' => $cursoId,
                'fecha_limite' => $inicio->copy()->addDays(8)->toDateString(),
                'fecha_envio'  => $inicio->copy()->addDays(7)->toDateString(),
            ]);
        }

        $envioFin = $fin->copy()->addDays(10);
        if ($envioFin->lte($hoy)) {
            DB::table('comunicacion_fundae')->insert([
                'tipo' => 'FINALIZACION', 'curso_id' => $cursoId,
                'fecha_limite' => $fin->copy()->addMonths(2)->toDateString(),
                'fecha_envio'  => $envioFin->toDateString(),
            ]);
        }
    }
}
