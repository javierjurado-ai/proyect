<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GrupoEmpresa;

class GrupoEmpresaSeeder extends Seeder
{
    public function run(): void
    {
        GrupoEmpresa::create([
            'grupo_idgrupo' => 1,
            'empresa_id' => 1
        ]);

        GrupoEmpresa::create([
            'grupo_idgrupo' => 1,
            'empresa_id' => 2
        ]);

        GrupoEmpresa::create([
            'grupo_idgrupo' => 2,
            'empresa_id' => 3
        ]);
    }
}