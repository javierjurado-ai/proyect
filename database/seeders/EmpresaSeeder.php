<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = [
            ['cif' => 'A12345678', 'nombre' => 'Tecnologías Norte S.A.', 'total_trabajadores' => 45],
            ['cif' => 'B87654321', 'nombre' => 'Construcciones Levante S.L.', 'total_trabajadores' => 120],
            ['cif' => 'B11223344', 'nombre' => 'Logística Centro S.L.', 'total_trabajadores' => 30],
        ];

        foreach ($empresas as $empresa) {
            Empresa::updateOrCreate(['cif' => $empresa['cif']], $empresa);
        }
    }
}