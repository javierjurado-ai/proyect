<?php

namespace Database\Seeders;

use App\Models\ComunicacionFundae;
use App\Models\Curso;
use App\Models\Enums\Tipo;
use Illuminate\Database\Seeder;

class ComunicacionFundaeSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = Tipo::cases();

        foreach (Curso::all() as $curso) {
            $limite = now()->addDays(rand(5, 30));

            ComunicacionFundae::create([
                'grupo_idgrupo' => $curso->idgrupo,
                'tipo' => $tipos[array_rand($tipos)],
                'fecha_limite' => $limite->toDateString(),
                'fecha_envio' => $limite->subDays(rand(1, 4))->toDateString(),
                'referencia' => 'REF-' . strtoupper(substr(uniqid(), -6)),
            ]);
        }
    }
}