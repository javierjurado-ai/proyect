<?php
 
namespace Database\Seeders;
 
use App\Models\Centro;
use App\Models\Empresa;
use Illuminate\Database\Seeder;
 
class CentroSeeder extends Seeder
{
    public function run(): void
    {
        // empresa_id apunta a empresa.id: sin empresas no se puede sembrar.
        $empresas = Empresa::pluck('id');
 
        if ($empresas->isEmpty()) {
            $this->command->warn('No hay empresas. Ejecuta antes el seeder de Empresa.');
            return;
        }
 
        $centros = [
            ['nombre' => 'Centro Madrid Norte',  'responsable' => 'Ana López',      'direccion' => 'Calle Mayor 12, Madrid',        'telefono' => '910000001'],
            ['nombre' => 'Centro Madrid Sur',    'responsable' => 'Pedro Martín',   'direccion' => 'Avenida de Andalucía 45, Madrid', 'telefono' => '910000002'],
            ['nombre' => 'Centro Alcalá',        'responsable' => 'Lucía Fernández', 'direccion' => 'Calle Libreros 8, Alcalá de Henares', 'telefono' => '910000003'],
            ['nombre' => 'Centro Getafe',        'responsable' => 'Sergio Navarro', 'direccion' => 'Calle Madrid 20, Getafe',       'telefono' => '910000004'],
            ['nombre' => 'Centro Móstoles',      'responsable' => 'Elena Ramos',    'direccion' => 'Avenida Portugal 3, Móstoles',  'telefono' => '910000005'],
            ['nombre' => 'Centro Leganés',       'responsable' => 'Raúl Ortega',    'direccion' => 'Calle Butarque 15, Leganés',    'telefono' => '910000006'],
        ];
 
        foreach ($centros as $i => $datos) {
            Centro::updateOrCreate(
                ['nombre' => $datos['nombre']],
                $datos + [
                    'empresa_id' => $empresas->random(),
                    'email'      => 'centro' . ($i + 1) . '@ejemplo.com',
                ]
            );
        }
    }
}
 