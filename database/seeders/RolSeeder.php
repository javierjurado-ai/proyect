<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rol')->insert([
            [
                'id'          => 1,
                'rol'         => 'ADMINISTRADOR',
                'rol_empresa' => null,
            ],
            [
                'id'          => 2,
                'rol'         => 'COORDINADOR',
                'rol_empresa' => null,
            ],
            [
                'id'          => 3,
                'rol'         => 'COMERCIAL',
                'rol_empresa' => null,
            ],
            [
                'id'          => 4,
                'rol'         => 'LOGISTA',
                'rol_empresa' => null,
            ],
            [
                'id'          => 5,
                'rol'         => 'DOCENTE',
                'rol_empresa' => null,
            ],
            [
                'id'          => 6,
                'rol'         => 'ALUMNO',
                'rol_empresa' => null,
            ],
            [
                'id'          => 7,
                'rol'         => null,
                'rol_empresa' => 'FORMADORA',
            ],
            [
                'id'          => 8,
                'rol'         => null,
                'rol_empresa' => 'CLIENTE',
            ],
            [
                'id'          => 9,
                'rol'         => null,
                'rol_empresa' => 'PROVEEDOR',
            ],
        ]);
    }
}
