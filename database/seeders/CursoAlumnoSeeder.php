<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\CursoAlumno;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class CursoAlumnoSeeder extends Seeder
{
    public function run(): void
    {
        $personas = Persona::all();

        foreach (Curso::all() as $curso) {
            $alumnos = $personas->random(min(3, $personas->count()));

            foreach ($alumnos as $persona) {
                CursoAlumno::firstOrCreate([
                    'grupo_idgrupo' => $curso->idgrupo,
                    'persona_idusuario' => $persona->idusuario,
                ]);
            }
        }
    }
}