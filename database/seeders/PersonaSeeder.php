<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\enums\Genero;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = ['Ana', 'Luis', 'Marta', 'Carlos', 'Lucía', 'Pablo', 'Elena', 'Javier'];
        $apellidos = ['García', 'Martínez', 'López', 'Sánchez', 'Pérez', 'Gómez', 'Ruiz', 'Díaz'];
        $generos = Genero::cases();
        $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $n = 1;

        foreach (Empresa::all() as $empresa) {
            for ($i = 0; $i < 3; $i++, $n++) {
                $numero = 10000000 + $n;
                $nombre = $nombres[array_rand($nombres)];

                Persona::create([
                    'empresa_id' => $empresa->id,
                    'nombre' => $nombre,
                    'primer_apellido' => $apellidos[array_rand($apellidos)],
                    'segundo_apellido' => $apellidos[array_rand($apellidos)],
                    'fecha_nacimiento' => now()->subYears(rand(20, 60))->subDays(rand(0, 364))->toDateString(),
                    'genero' => $generos[array_rand($generos)],
                    'nacionalidad' => 'Española',
                    'telefono' => '6' . rand(10000000, 99999999),
                    'direccion' => 'Calle Ejemplo ' . rand(1, 100),
                    'email' => strtolower(\Illuminate\Support\Str::ascii($nombre)) . $n . '@ejemplo.com',
                    'DNI' => $numero . $letras[$numero % 23],
                    'NIE' => null,
                    'numero_seguridad_social' => '28' . rand(10000000, 99999999),
                    'LOPD' => true,
                ]);
            }
        }
    }
}