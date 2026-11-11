<?php

namespace Database\Seeders;

use App\Models\Enums\Estado;
use App\Models\PeticionesLogistica;
use Illuminate\Database\Seeder;

class PeticionesLogisticaSeeder extends Seeder
{
    public function run(): void
    {
        $estados = Estado::cases();

        for ($i = 1; $i <= 5; $i++) {
            PeticionesLogistica::create([
                'fecha' => now()->subDays(rand(0, 30))->toDateString(),
                'estado' => $estados[array_rand($estados)],
                'observaciones' => "Petición de prueba $i",
            ]);
        }
    }
}