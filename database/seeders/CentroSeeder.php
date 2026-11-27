<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\AyudasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Centros de formación, sus contactos y sus aulas.
 * Dos centros propios (INTERNO) y dos en instalaciones de clientes (EXTERNO).
 */
class CentroSeeder extends Seeder
{
    use AyudasSeeder;

    public const CENTROS = [
        'MAD' => [
            'nombre' => 'Aula Activa - Sede Madrid', 'direccion' => 'Calle de Bravo Murillo 145', 'telefono' => '910456780',
            'email' => 'sede.madrid@aulaactiva.test', 'provincia' => 'Madrid', 'municipio' => 'Madrid', 'cp' => '28020',
            'tipo' => 'INTERNO', 'empresa' => 'AULA',
            'contactos' => [
                ['Beatriz Lozano Peña', 'RESPONSABLE', '611203344', 'beatriz.lozano@aulaactiva.test'],
                ['Óscar Pastor Medina', 'COORDINADOR', '611203345', 'oscar.pastor@aulaactiva.test'],
            ],
            'aulas' => [
                ['Aula 1', 20, 'TEORICO', 48, 'Pizarra digital y proyector fijo'],
                ['Aula 2', 16, 'TEORICO', 40, null],
                ['Aula de informática', 15, 'TEORICO', 45, '15 puestos con ordenador'],
                ['Taller polivalente', 12, 'TALLER', 70, 'Toma de corriente trifásica'],
            ],
        ],
        'GET' => [
            'nombre' => 'Aula Activa - Getafe', 'direccion' => 'Avenida de John Lennon 22', 'telefono' => '916821190',
            'email' => 'getafe@aulaactiva.test', 'provincia' => 'Madrid', 'municipio' => 'Getafe', 'cp' => '28906',
            'tipo' => 'INTERNO', 'empresa' => 'AULA',
            'contactos' => [
                ['Iván Cordero Ruiz', 'RESPONSABLE', '622418870', 'ivan.cordero@aulaactiva.test'],
            ],
            'aulas' => [
                ['Aula A', 18, 'TEORICO', 42, null],
                ['Taller de mecánica', 10, 'TALLER', 120, 'Elevador y banco de pruebas'],
                ['Patio de maniobras', 8, 'TALLER', 300, 'Zona exterior para carretillas'],
            ],
        ],
        'SEV' => [
            'nombre' => 'Hotel Mirador del Sur (aula cliente)', 'direccion' => 'Paseo de las Delicias 9', 'telefono' => '954238100',
            'email' => 'formacion@hotelmiradorsur.test', 'provincia' => 'Sevilla', 'municipio' => 'Sevilla', 'cp' => '41013',
            'tipo' => 'EXTERNO', 'empresa' => 'HOTEL',
            'contactos' => [
                ['Alicia Romero Vega', 'RESPONSABLE', '655781203', 'alicia.romero@hotelmiradorsur.test'],
            ],
            'aulas' => [
                ['Salón Giralda', 25, 'TEORICO', 60, 'Sala de reuniones del hotel'],
            ],
        ],
        'VAL' => [
            'nombre' => 'La Huerta - Sala de formación', 'direccion' => 'C/ Sant Vicent Màrtir 250', 'telefono' => '963781540',
            'email' => 'formacion@superlahuerta.test', 'provincia' => 'Valencia', 'municipio' => 'Valencia', 'cp' => '46017',
            'tipo' => 'EXTERNO', 'empresa' => 'SUPER',
            'contactos' => [
                ['Vicent Ferrer Puig', 'RESPONSABLE', '678332190', 'vicent.ferrer@superlahuerta.test'],
                ['Amparo Soler Mas', 'COORDINADOR', '678332191', 'amparo.soler@superlahuerta.test'],
            ],
            'aulas' => [
                ['Sala de formación', 20, 'TEORICO', 50, null],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::CENTROS as $c) {
            $centroId = $this->insertar('centro', [
                'nombre'            => $c['nombre'],
                'direccion'         => $c['direccion'],
                'telefono'          => $c['telefono'],
                'email'             => $c['email'],
                'provincia'         => $c['provincia'],
                'municipio'         => $c['municipio'],
                'codigo_postal'     => $c['cp'],
                'numero_total_aula' => count($c['aulas']),
                'tipo_centro'       => $c['tipo'],
                'empresa_id'        => $this->empresaId($c['empresa']),
            ]);

            foreach ($c['contactos'] as [$nombre, $tipo, $telefono, $email]) {
                DB::table('centro_contactos')->insert([
                    'nombre'            => $nombre,
                    'contacto'          => $tipo,
                    'telefono_personal' => $telefono,
                    'email_personal'    => $email,
                    'centro_id'         => $centroId,
                    'curso_id'          => null,
                ]);
            }

            // Las aulas se crean DISPONIBLES; CursoSeeder marca como OCUPADAS las de los cursos en marcha
            foreach ($c['aulas'] as [$nombre, $capacidad, $uso, $m2, $obs]) {
                DB::table('aulas')->insert([
                    'nombre_aula'   => $nombre,
                    'capacidad'     => $capacidad,
                    'estado'        => 'DISPONIBLE',
                    'uso_aula'      => $uso,
                    'dimension'     => $m2,
                    'observaciones' => $obs,
                    'curso_id'      => null,
                    'centro_id'     => $centroId,
                ]);
            }
        }
    }
}
