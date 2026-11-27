<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Datos de prueba de la aplicación.
 *
 *   php artisan migrate:fresh --seed
 *
 * Usuarios de acceso (con las contraseñas de siempre): admin, coordinador, comercial y logista.
 *
 * Las fechas son relativas al día en que se ejecuta: cada vez que se regenera la base de datos
 * hay cursos terminados, en marcha, que empiezan esta semana y futuros.
 * El orden importa: cada seeder usa datos de los anteriores.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Catálogos
            AreaProfesionalSeeder::class,
            RolSeeder::class,
            SerieFacturaSeeder::class,

            // Maestros
            EmpresaSeeder::class,
            CentroSeeder::class,
            PersonaSeeder::class,
            FormacionSeeder::class,
            AlmacenSeeder::class,

            // Actividad
            CursoSeeder::class,
            CreditoFormativoSeeder::class,
            LogisticaSeeder::class,
            FacturacionSeeder::class,
        ]);
    }
}
