<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        Curso::create([
            'curso_idcurso' => 1,
            'codigo_grupo' => 'GRUPO-001',
            'empresa_formadora' => 'Empresa de formación A',
            'fecha_inicio' => '2026-11-01',
            'fecha_fin' => '2026-12-30',
            'hora_inicio' => '09:00:00',
            'hora_final' => '14:00:00',
            'turno' => 'Mañana',
            'aula' => 'Aula 1',
            'aula_virtual' => 'https://example.com/aula1',
            'tecnico_comunidad' => 'Técnico 1',
            'observaciones' => 'Curso de prueba 1',
            'peticiones_logistica_id' => null,
            'centro_id' => null,
            'presupuesto_id' => null,
        ]);

        Curso::create([
            'curso_idcurso' => 2,
            'codigo_grupo' => 'GRUPO-002',
            'empresa_formadora' => 'Empresa de formación B',
            'fecha_inicio' => '2026-11-15',
            'fecha_fin' => '2027-01-15',
            'hora_inicio' => '16:00:00',
            'hora_final' => '20:00:00',
            'turno' => 'Tarde',
            'aula' => 'Aula 2',
            'aula_virtual' => 'https://example.com/aula2',
            'tecnico_comunidad' => 'Técnico 2',
            'observaciones' => 'Curso de prueba 2',
            'peticiones_logistica_id' => null,
            'centro_id' => null,
            'presupuesto_id' => null,
        ]);
        
        $this->command->info('Cursos creados correctamente.');
    }
}