<?php

namespace Database\Seeders;

use App\Models\PeticionesLogistica;
use App\Models\PeticionProducto;
use Illuminate\Database\Seeder;

class PeticionProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = ['Portátil', 'Proyector', 'Pizarra', 'Material de oficina', 'Auriculares'];

        foreach (PeticionesLogistica::all() as $peticion) {
            foreach (collect($productos)->random(2) as $producto) {
                PeticionProducto::create([
                    'peticiones_logistica_id' => $peticion->id,
                    'producto' => $producto,
                    'cantidad' => rand(1, 20),
                ]);
            }
        }
    }
}