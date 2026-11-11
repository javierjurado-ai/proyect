<?php

namespace Database\Seeders;

use App\Models\CreditoFormativo;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class CreditoFormativoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Empresa::all() as $empresa) {
            foreach ([now()->year - 1, now()->year] as $anio) {
                $total = rand(5, 50) * 100;

                CreditoFormativo::create([
                    'empresa_id' => $empresa->id,
                    'año' => $anio,
                    'importe_total' => $total,
                    'importe_consumido' => rand(0, $total),
                ]);
            }
        }
    }
}