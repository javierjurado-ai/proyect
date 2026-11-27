<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\AyudasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Personas: personal de la formadora (con usuario), docentes y alumnos.
 *
 * Los alumnos son trabajadores de las empresas cliente: su email lleva el dominio de su empresa
 * (p. ej. @transportesnavarro.test). CursoSeeder los busca así para matricularlos.
 */
class PersonaSeeder extends Seeder
{
    use AyudasSeeder;

    /**
     * Personal con acceso. Se conservan los mismos usuarios y contraseñas de siempre.
     */
    public const PERSONAL = [
        'admin' => ['Laura', 'Gómez', 'Ruiz', 'MUJER', '1984-03-12', 'admin@aulaactiva.test', 'ADMINISTRADOR',
            '$2y$12$JRBZ585AJVjxOEtTFJvQouUqI6xllbPW1ZVTfjnuxuqhBLLnVfBjq'],
        'coordinador' => ['Carlos', 'Martín', 'Prieto', 'HOMBRE', '1979-11-02', 'coordinador@aulaactiva.test', 'COORDINADOR',
            '$2y$12$qkLrUvDqoQnL1zzpNGVv.uEA87WVHCtzZueLSGOcS1cHFyWQsGVV6'],
        'comercial' => ['Marta', 'Sanz', 'Herrero', 'MUJER', '1990-06-25', 'comercial@aulaactiva.test', 'COMERCIAL',
            '$2y$12$LR.GLXR.Ib8KN1cNnRGKS.GFJBAWweigx6iCo8OGh0jqVDRJkUN2u'],
        'logista' => ['Lola', 'Navas', 'Ortega', 'MUJER', '1987-01-30', 'logistica@aulaactiva.test', 'LOGISTA',
            '$2y$12$jljl8QNZqWlN0zHmn9oaPeJamMfqxfM5xz0fEImmaoWdgLbEBM4ui'],
    ];

    /**
     * Docentes. La empresa es la que les contrata (en autónomos, la formadora que les subcontrata).
     */
    public const DOCENTES = [
        'D1' => ['Rosa', 'Delgado', 'Marín', 'MUJER', '1975-04-18', 'rosa.delgado@aulaactiva.test', 'Española',
            'empresa' => 'AULA', 'categoria' => 'CUENTA AJENA', 'contrato' => 'INDEFINIDO', 'precio_hora' => 22.0],
        'D2' => ['Javier', 'Ortega', 'Blanco', 'HOMBRE', '1982-09-07', 'javier.ortega.formacion@correo.test', 'Española',
            'empresa' => 'AULA', 'categoria' => 'AUTONOMO', 'contrato' => 'OBRA Y SERVICIO', 'precio_hora' => 48.0],
        'D3' => ['Nuria', 'Campos', 'Vidal', 'MUJER', '1988-12-01', 'nuria.campos@aulaactiva.test', 'Española',
            'empresa' => 'AULA', 'categoria' => 'CUENTA AJENA', 'contrato' => 'FIJO DISCONTINUO', 'precio_hora' => 20.0],
        'D4' => ['Daniel', 'Wilson', 'Pérez', 'HOMBRE', '1986-02-14', 'daniel.wilson@aulaactiva.test', 'Británica',
            'empresa' => 'AULA', 'categoria' => 'CUENTA AJENA', 'contrato' => 'TIEMPO COMPLETO', 'precio_hora' => 21.0, 'nie' => ['Y', 3456789]],
        'D5' => ['Elena', 'Ríos', 'Castro', 'MUJER', '1980-07-22', 'elena.rios.docente@correo.test', 'Española',
            'empresa' => 'AULA', 'categoria' => 'AUTONOMO', 'contrato' => 'OBRA Y SERVICIO', 'precio_hora' => 45.0],
        'D6' => ['Andrés', 'Molina', 'Gil', 'HOMBRE', '1972-05-09', 'andres.molina@itlevante.test', 'Española',
            'empresa' => 'ITL', 'categoria' => 'CUENTA AJENA', 'contrato' => 'INDEFINIDO', 'precio_hora' => 23.0],
        'D7' => ['Raúl', 'Ibáñez', 'Soto', 'HOMBRE', '1983-10-28', 'raul.ibanez@aulaactiva.test', 'Española',
            'empresa' => 'AULA', 'categoria' => 'CUENTA AJENA', 'contrato' => 'OBRA Y SERVICIO', 'precio_hora' => 24.0],
        'D8' => ['Sofía', 'Herrera', 'León', 'MUJER', '1991-03-03', 'sofia.herrera@aulaactiva.test', 'Española',
            'empresa' => 'AULA', 'categoria' => 'CUENTA AJENA', 'contrato' => 'INDEFINIDO', 'precio_hora' => 21.0],
    ];

    /** Cuántos trabajadores (alumnos) se crean de cada empresa cliente. */
    public const ALUMNOS_POR_EMPRESA = [
        'TRANS' => 12, 'DENT' => 6, 'HOTEL' => 12, 'CONS' => 9,
        'SUPER' => 16, 'TALL' => 7, 'ASES' => 5, 'PAN' => 6,
    ];

    private const NOMBRES_M = ['Alejandro', 'David', 'Pablo', 'Sergio', 'Jorge', 'Adrián', 'Álvaro', 'Diego', 'Rubén', 'Mario',
        'Iván', 'Héctor', 'Víctor', 'Hugo', 'Gonzalo', 'Marcos', 'Rafael', 'Antonio', 'Manuel', 'Francisco'];
    private const NOMBRES_F = ['María', 'Lucía', 'Paula', 'Sara', 'Carmen', 'Andrea', 'Irene', 'Cristina', 'Patricia', 'Raquel',
        'Silvia', 'Noelia', 'Marina', 'Beatriz', 'Alba', 'Elena', 'Rocío', 'Pilar', 'Inés', 'Julia'];
    private const APELLIDOS = ['García', 'Fernández', 'González', 'Rodríguez', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Martín',
        'Jiménez', 'Hernández', 'Díaz', 'Moreno', 'Muñoz', 'Álvarez', 'Romero', 'Alonso', 'Gutiérrez', 'Navarro',
        'Torres', 'Domínguez', 'Vázquez', 'Ramos', 'Gil', 'Ramírez', 'Serrano', 'Blanco', 'Molina', 'Morales'];
    private const CALLES = ['Calle Mayor', 'Calle Real', 'Avenida de la Constitución', 'Calle del Sol', 'Calle de la Paz',
        'Avenida de Andalucía', 'Calle San Juan', 'Calle Nueva', 'Plaza de España', 'Calle del Carmen'];

    /** Algunos alumnos extranjeros (con NIE): posición dentro de la lista global => [nacionalidad, prefijo, número] */
    private const EXTRANJEROS = [
        5  => ['Rumana', 'X', 4512871],
        19 => ['Marroquí', 'Y', 2087734],
        33 => ['Ecuatoriana', 'X', 9031255],
        48 => ['Colombiana', 'Y', 6624019],
        61 => ['Ucraniana', 'Z', 1150378],
    ];

    public function run(): void
    {
        $nss = 280100000000;   // nº de la Seguridad Social: 28 + 10 cifras

        // --- Personal con usuario ---
        $i = 0;
        foreach (self::PERSONAL as $usuario => [$nombre, $ap1, $ap2, $genero, $nacimiento, $email, $rol, $hash]) {
            $i++;
            $personaId = $this->insertar('persona', [
                'nombre' => $nombre, 'primer_apellido' => $ap1, 'segundo_apellido' => $ap2,
                'fecha_nacimiento' => $nacimiento, 'genero' => $genero, 'nacionalidad' => 'Española',
                'telefono' => '60010000' . $i, 'direccion' => 'Calle de Bravo Murillo 145, Madrid',
                'email' => $email, 'DNI' => $this->dni(51000000 + $i * 7919), 'NIE' => null,
                'numero_seguridad_social' => (string) ($nss + $i), 'LOPD' => 'SI',
            ]);

            DB::table('rol_persona')->insert(['persona_id' => $personaId, 'rol_id' => $this->rolId($rol)]);
            DB::table('usuario')->insert(['usuario' => $usuario, 'contrasena' => $hash, 'persona_id' => $personaId]);
        }

        // --- Docentes (el rol DOCENTE se lo da la app al asignarles un curso; aquí ya lo tienen) ---
        $i = 0;
        foreach (self::DOCENTES as $d) {
            $i++;
            [$nombre, $ap1, $ap2, $genero, $nacimiento, $email, $nacionalidad] = $d;
            $nie = isset($d['nie']) ? $this->nie($d['nie'][0], $d['nie'][1]) : null;

            $personaId = $this->insertar('persona', [
                'nombre' => $nombre, 'primer_apellido' => $ap1, 'segundo_apellido' => $ap2,
                'fecha_nacimiento' => $nacimiento, 'genero' => $genero, 'nacionalidad' => $nacionalidad,
                'telefono' => sprintf('6202%05d', $i * 1311), 'direccion' => self::CALLES[$i % 10] . ' ' . (10 + $i) . ', Madrid',
                'email' => $email, 'DNI' => $nie ? null : $this->dni(52000000 + $i * 6421), 'NIE' => $nie,
                'numero_seguridad_social' => (string) ($nss + 100 + $i), 'LOPD' => 'SI',
            ]);

            DB::table('rol_persona')->insert(['persona_id' => $personaId, 'rol_id' => $this->rolId('DOCENTE')]);
        }

        // --- Alumnos: trabajadores de las empresas cliente ---
        $n = 0;
        $usados = [];
        foreach (self::ALUMNOS_POR_EMPRESA as $clave => $cuantos) {
            $empresa = EmpresaSeeder::EMPRESAS[$clave];

            for ($k = 0; $k < $cuantos; $k++, $n++) {
                $mujer  = $n % 2 === 0;
                $nombre = $mujer ? self::NOMBRES_F[($n * 7) % 20] : self::NOMBRES_M[($n * 3) % 20];
                $ap1    = self::APELLIDOS[($n * 7 + 1) % 30];
                $ap2    = self::APELLIDOS[($n * 11 + 4) % 30];

                $local = Str::slug($nombre, '') . '.' . Str::slug($ap1, '');
                while (isset($usados[$local . '@' . $empresa['dominio']])) {
                    $local .= $n;
                }
                $email = $local . '@' . $empresa['dominio'];
                $usados[$email] = true;

                $extranjero = self::EXTRANJEROS[$n] ?? null;

                $personaId = $this->insertar('persona', [
                    'nombre' => $nombre, 'primer_apellido' => $ap1, 'segundo_apellido' => $extranjero ? null : $ap2,
                    'fecha_nacimiento' => sprintf('%d-%02d-%02d', 1966 + ($n * 7) % 37, 1 + $n % 12, 1 + ($n * 5) % 28),
                    'genero' => $mujer ? 'MUJER' : 'HOMBRE',
                    'nacionalidad' => $extranjero[0] ?? 'Española',
                    'telefono' => sprintf('6%08d', 30000000 + $n * 104729 % 69999999),
                    'direccion' => self::CALLES[$n % 10] . ' ' . (1 + ($n * 13) % 120) . ', ' . $empresa['municipio'],
                    'email' => $email,
                    'DNI' => $extranjero ? null : $this->dni(20000000 + $n * 104729 % 59999999),
                    'NIE' => $extranjero ? $this->nie($extranjero[1], $extranjero[2]) : null,
                    'numero_seguridad_social' => (string) ($nss + 1000 + $n),
                    'LOPD' => $n % 17 === 9 ? 'NO' : 'SI',   // un par sin consentimiento LOPD, para probar avisos
                ]);

                DB::table('rol_persona')->insert(['persona_id' => $personaId, 'rol_id' => $this->rolId('ALUMNO')]);
            }
        }
    }

    /** Ids de los alumnos de una empresa cliente, en orden de creación. */
    public static function alumnosDe(string $clave): array
    {
        return DB::table('persona')
            ->where('email', 'like', '%@' . EmpresaSeeder::EMPRESAS[$clave]['dominio'])
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }
}
