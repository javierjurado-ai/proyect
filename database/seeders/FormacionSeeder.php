<?php
namespace Database\Seeders;

use App\Models\Enums\Modalidad;
use App\Models\Formacion;
use Illuminate\Database\Seeder;

class FormacionSeeder extends Seeder
{
    public function run(): void
    {
        $cursos = [
            [
                'nombre' => 'Excel avanzado',
                'codigo_accion' => 'AF-001',
                'modalidad' => Modalidad::Presencial,
                'sector' => 'Administración',
                'horas_totales' => 40,
                'activo' => true,
            ],
            [
                'nombre' => 'Prevención de riesgos laborales',
                'codigo_accion' => 'AF-002',
                'modalidad' => Modalidad::Mixta,
                'sector' => 'Industria',
                'horas_totales' => 60,
                'activo' => true,
            ],
        ];

        foreach ($cursos as $curso) {
            Formacion::create($curso);
        }
    }
}