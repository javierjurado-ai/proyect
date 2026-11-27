<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\AyudasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo de formaciones (acciones formativas).
 * Los objetivos y contenidos no están en la tabla formacion: CursoSeeder los usa para rellenar cada curso.
 */
class FormacionSeeder extends Seeder
{
    use AyudasSeeder;

    // codigo_accion => [nombre, código del área profesional, horas, activo, objetivos, contenido]
    public const FORMACIONES = [
        1001 => ['Prevención de riesgos laborales (nivel básico)', 'FCOS', 60, true,
            'Conocer los riesgos generales y específicos del puesto y aplicar las medidas preventivas y de emergencia según la Ley 31/1995.',
            "1. Conceptos básicos de seguridad y salud\n2. Riesgos generales y su prevención\n3. Riesgos específicos del sector\n4. Gestión de la prevención\n5. Primeros auxilios y emergencias"],
        1002 => ['Manipulador de alimentos', 'FCOM', 10, true,
            'Aplicar las prácticas correctas de higiene en la manipulación de alimentos y prevenir las toxiinfecciones alimentarias.',
            "1. Contaminación de los alimentos\n2. Toxiinfecciones alimentarias\n3. Higiene personal y de las instalaciones\n4. APPCC y trazabilidad"],
        1003 => ['Excel avanzado', 'ADGG', 30, true,
            'Dominar funciones avanzadas, tablas dinámicas y automatización básica para el análisis de datos en la empresa.',
            "1. Funciones lógicas y de búsqueda\n2. Tablas y gráficos dinámicos\n3. Validación de datos y formato condicional\n4. Introducción a las macros"],
        1004 => ['Inglés para atención al cliente', 'FCOE', 60, true,
            'Atender a clientes en inglés de forma presencial, por teléfono y por correo, resolviendo consultas e incidencias habituales.',
            "1. Saludos, presentaciones y cortesía\n2. Atención telefónica\n3. Gestión de reservas y pedidos\n4. Quejas y reclamaciones\n5. Correspondencia comercial"],
        1005 => ['Marketing digital y redes sociales', 'COMM', 40, true,
            'Diseñar y ejecutar un plan básico de marketing digital y medir sus resultados.',
            "1. Plan de marketing digital\n2. Posicionamiento web (SEO/SEM)\n3. Redes sociales para empresas\n4. Email marketing\n5. Analítica y KPI"],
        1006 => ['Manejo seguro de carretillas elevadoras', 'COML', 20, true,
            'Conducir carretillas elevadoras de forma segura cumpliendo la normativa (UNE 58451).',
            "1. Normativa y responsabilidades\n2. Tipos de carretillas y componentes\n3. Estabilidad y manipulación de cargas\n4. Prácticas de conducción"],
        1007 => ['Primeros auxilios en el trabajo', 'SANT', 12, true,
            'Actuar correctamente ante una emergencia en el trabajo hasta la llegada de los servicios sanitarios.',
            "1. Conducta PAS\n2. Soporte vital básico y DEA\n3. Hemorragias, heridas y quemaduras\n4. Traumatismos e inmovilizaciones"],
        1008 => ['Atención al cliente y gestión de reclamaciones', 'COMT', 20, true,
            'Mejorar la calidad del servicio y gestionar reclamaciones de forma eficaz y empática.',
            "1. Comunicación con el cliente\n2. Tipos de clientes\n3. Gestión de quejas y reclamaciones\n4. Fidelización"],
        1009 => ['Ciberseguridad para usuarios', 'IFCT', 16, true,
            'Identificar las amenazas más habituales (phishing, malware, ingeniería social) y aplicar buenas prácticas de seguridad.',
            "1. Amenazas actuales\n2. Contraseñas y doble factor\n3. Correo y navegación segura\n4. Protección de dispositivos\n5. Qué hacer ante un incidente"],
        1010 => ['Protección de datos (RGPD) en la empresa', 'ADGD', 12, true,
            'Aplicar el RGPD y la LOPDGDD en la gestión diaria de datos personales.',
            "1. Principios del RGPD\n2. Derechos de los interesados\n3. Registro de actividades y medidas de seguridad\n4. Brechas de seguridad"],
        1011 => ['Igualdad de oportunidades en la empresa', 'FCOO', 10, true,
            'Conocer el marco normativo de igualdad y las herramientas para implantar un plan de igualdad.',
            "1. Conceptos básicos de igualdad\n2. Marco normativo\n3. Planes de igualdad\n4. Prevención del acoso"],
        1012 => ['Diagnosis de sistemas eléctricos del vehículo', 'TMVG', 40, true,
            'Diagnosticar y reparar averías de los sistemas eléctricos y electrónicos del vehículo con equipos de diagnosis.',
            "1. Electricidad del automóvil\n2. Esquemas eléctricos\n3. Equipos de diagnosis OBD\n4. Redes multiplexadas (CAN)\n5. Casos prácticos"],
        1013 => ['Gestión de alérgenos en hostelería', 'HOTR', 8, true,
            'Gestionar los 14 alérgenos de declaración obligatoria y evitar la contaminación cruzada.',
            "1. Normativa (Reglamento 1169/2011)\n2. Los 14 alérgenos\n3. Contaminación cruzada\n4. Información al cliente"],
        // Formación descatalogada: sirve para probar el filtro de "activo"
        1014 => ['Liderazgo y gestión de equipos', 'ADGD', 20, false,
            'Desarrollar habilidades de liderazgo para dirigir y motivar equipos de trabajo.',
            "1. Estilos de liderazgo\n2. Motivación\n3. Delegación\n4. Gestión de conflictos"],
    ];

    public function run(): void
    {
        foreach (self::FORMACIONES as $codigo => [$nombre, $area, $horas, $activo]) {
            DB::table('formacion')->insert([
                'nombre'              => $nombre,
                'codigo_accion'       => $codigo,
                'horas_totales'       => $horas,
                'activo'              => $activo ? 1 : 0,
                'area_profesional_id' => $this->idDe('area_profesional', ['codigo' => $area]),
            ]);
        }
    }
}
