<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaProfesionalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('area_profesional')->insert([
            [
                'id'          => 1,
                'codigo'      => 'ADGD',
                'descripcion' => 'ADMINISTRACIÓN Y AUDITORÍA',
            ],
            [
                'id'          => 2,
                'codigo'      => 'ADGG',
                'descripcion' => 'GESTIÓN DE LA INFORMACIÓN Y COMUNICACIÓN',
            ],
            [
                'id'          => 3,
                'codigo'      => 'ADGN',
                'descripcion' => 'FINANZAS Y SEGUROS',
            ],
            [
                'id'          => 4,
                'codigo'      => 'AFDA',
                'descripcion' => 'ACTIVIDADES FÍSICO DEPORTIVAS RECREATIVAS',
            ],
            [
                'id'          => 5,
                'codigo'      => 'AFDP',
                'descripcion' => 'PREVENCIÓN Y RECUPERACION',
            ],
            [
                'id'          => 6,
                'codigo'      => 'AGAJ',
                'descripcion' => 'JARDINERÍA',
            ],
            [
                'id'          => 7,
                'codigo'      => 'AGAN',
                'descripcion' => 'GANADERÍA',
            ],
            [
                'id'          => 8,
                'codigo'      => 'AGAR',
                'descripcion' => 'FORESTAL',
            ],
            [
                'id'          => 9,
                'codigo'      => 'AGAU',
                'descripcion' => 'AGRICULTURA',
            ],
            [
                'id'          => 10,
                'codigo'      => 'ARGA',
                'descripcion' => 'ACTIVIDADES Y TÉCNICAS GRÁFICAS ARTÍSTICAS',
            ],
            [
                'id'          => 11,
                'codigo'      => 'ARGC',
                'descripcion' => 'ENCUADERNACIÓN INDUSTRIAL',
            ],
            [
                'id'          => 12,
                'codigo'      => 'ARGG',
                'descripcion' => 'DISEÑO GRÁFICO Y MULTIMEDIA',
            ],
            [
                'id'          => 13,
                'codigo'      => 'ARGI',
                'descripcion' => 'IMPRESIÓN',
            ],
            [
                'id'          => 14,
                'codigo'      => 'ARGN',
                'descripcion' => 'EDICIÓN',
            ],
            [
                'id'          => 15,
                'codigo'      => 'ARGP',
                'descripcion' => 'PREIMPRESIÓN',
            ],
            [
                'id'          => 16,
                'codigo'      => 'ARGT',
                'descripcion' => 'TRANSFORMACION Y CONVERSIÓN EN INDUSTRIAS GRÁFICAS',
            ],
            [
                'id'          => 17,
                'codigo'      => 'ARTA',
                'descripcion' => 'ARTESANÍA TRADICIONAL',
            ],
            [
                'id'          => 18,
                'codigo'      => 'ARTB',
                'descripcion' => 'JOYERÍA Y ORFEBRERÍA',
            ],
            [
                'id'          => 19,
                'codigo'      => 'ARTG',
                'descripcion' => 'FABRICACIÓN Y MANTENIMIENTO DE INSTRUMENTOS MUSICALES',
            ],
            [
                'id'          => 20,
                'codigo'      => 'ARTN',
                'descripcion' => 'VIDRIO Y CERÁMICA ARTESANAL',
            ],
            [
                'id'          => 21,
                'codigo'      => 'ARTR',
                'descripcion' => 'RECUPERACIÓN, REPARACIÓN Y MANTENIMIENTO ARTÍSTICOS',
            ],
            [
                'id'          => 22,
                'codigo'      => 'ARTU',
                'descripcion' => 'ARTES ESCÉNICAS',
            ],
            [
                'id'          => 23,
                'codigo'      => 'COML',
                'descripcion' => 'LOGÍSTICA COMERCIAL Y GESTIÓN DEL TRANSPORTE',
            ],
            [
                'id'          => 24,
                'codigo'      => 'COMM',
                'descripcion' => 'MARKETING Y RELACIONES PÚBLICAS',
            ],
            [
                'id'          => 25,
                'codigo'      => 'COMT',
                'descripcion' => 'COMPRAVENTA',
            ],
            [
                'id'          => 26,
                'codigo'      => 'ELEE',
                'descripcion' => 'INSTALACIONES ELÉCTRICAS',
            ],
            [
                'id'          => 27,
                'codigo'      => 'ELEQ',
                'descripcion' => 'EQUIPOS ELECTRÓNICOS',
            ],
            [
                'id'          => 28,
                'codigo'      => 'ENAA',
                'descripcion' => 'AGUA',
            ],
            [
                'id'          => 29,
                'codigo'      => 'ENAE',
                'descripcion' => 'ENERGÍAS RENOVABLES',
            ],
            [
                'id'          => 30,
                'codigo'      => 'ENAL',
                'descripcion' => 'ENERGÍA ELÉCTRICA',
            ],
            [
                'id'          => 31,
                'codigo'      => 'ENAS',
                'descripcion' => 'GAS',
            ],
            [
                'id'          => 32,
                'codigo'      => 'EOCB',
                'descripcion' => 'ALBAÑILERÍA Y ACABADOS',
            ],
            [
                'id'          => 33,
                'codigo'      => 'EOCE',
                'descripcion' => 'ESTRUCTURAS',
            ],
            [
                'id'          => 34,
                'codigo'      => 'EOCJ',
                'descripcion' => 'COLOCACIÓN Y MONTAJE',
            ],
            [
                'id'          => 35,
                'codigo'      => 'EOCO',
                'descripcion' => 'PROYECTOS Y SEGUIMIENTO DE OBRAS',
            ],
            [
                'id'          => 36,
                'codigo'      => 'EOCQ',
                'descripcion' => 'MAQUINARIA DE CONSTRUCCIÓN',
            ],
            [
                'id'          => 37,
                'codigo'      => 'FCOA',
                'descripcion' => 'ASPECTOS MEDIOAMBIENTALES',
            ],
            [
                'id'          => 38,
                'codigo'      => 'FCOE',
                'descripcion' => 'LENGUAS EXTRANJERAS',
            ],
            [
                'id'          => 39,
                'codigo'      => 'FCOI',
                'descripcion' => 'INFORMÁTICA COMPLEMENTARIA',
            ],
            [
                'id'          => 40,
                'codigo'      => 'FCOL',
                'descripcion' => 'LENGUAS Y DIALECTOS ESPAÑOLES',
            ],
            [
                'id'          => 41,
                'codigo'      => 'FCOM',
                'descripcion' => 'MANIPULACIÓN ALIMENTARIA',
            ],
            [
                'id'          => 42,
                'codigo'      => 'FCOO',
                'descripcion' => 'ORIENTACIÓN LABORAL',
            ],
            [
                'id'          => 43,
                'codigo'      => 'FCOS',
                'descripcion' => 'SEGURIDAD Y SALUD LABORAL',
            ],
            [
                'id'          => 44,
                'codigo'      => 'FCOV',
                'descripcion' => 'COMPETENCIAS CLAVE',
            ],
            [
                'id'          => 45,
                'codigo'      => 'FMEC',
                'descripcion' => 'CONSTRUCCIONES METÁLICAS',
            ],
            [
                'id'          => 46,
                'codigo'      => 'FMEF',
                'descripcion' => 'FUNDICIÓN',
            ],
            [
                'id'          => 47,
                'codigo'      => 'FMEH',
                'descripcion' => 'OPERACIONES MECÁNICAS',
            ],
            [
                'id'          => 48,
                'codigo'      => 'FMEM',
                'descripcion' => 'PRODUCCIÓN MECÁNICA',
            ],
            [
                'id'          => 49,
                'codigo'      => 'HOTA',
                'descripcion' => 'ALOJAMIENTO',
            ],
            [
                'id'          => 50,
                'codigo'      => 'HOTJ',
                'descripcion' => 'JUEGOS DE AZAR',
            ],
            [
                'id'          => 51,
                'codigo'      => 'HOTR',
                'descripcion' => 'RESTAURACIÓN',
            ],
            [
                'id'          => 52,
                'codigo'      => 'HOTT',
                'descripcion' => 'TURISMO',
            ],
            [
                'id'          => 53,
                'codigo'      => 'HOTU',
                'descripcion' => 'AGROTURISMO',
            ],
            [
                'id'          => 54,
                'codigo'      => 'IEXD',
                'descripcion' => 'PIEDRA NATURAL',
            ],
            [
                'id'          => 55,
                'codigo'      => 'IEXM',
                'descripcion' => 'MINERÍA',
            ],
            [
                'id'          => 56,
                'codigo'      => 'IFCD',
                'descripcion' => 'DESARROLLO',
            ],
            [
                'id'          => 57,
                'codigo'      => 'IFCM',
                'descripcion' => 'COMUNICACIONES',
            ],
            [
                'id'          => 58,
                'codigo'      => 'IFCT',
                'descripcion' => 'SISTEMAS Y TELEMÁTICA',
            ],
            [
                'id'          => 59,
                'codigo'      => 'IMAI',
                'descripcion' => 'MONTAJE Y MANTENIMIENTO DE INSTALACIONES',
            ],
            [
                'id'          => 60,
                'codigo'      => 'IMAQ',
                'descripcion' => 'MAQUINARIA Y EQUIPO INDUSTRIAL',
            ],
            [
                'id'          => 61,
                'codigo'      => 'IMAR',
                'descripcion' => 'FRÍO Y CLIMATIZACIÓN',
            ],
            [
                'id'          => 62,
                'codigo'      => 'IMPE',
                'descripcion' => 'ESTÉTICA',
            ],
            [
                'id'          => 63,
                'codigo'      => 'IMPQ',
                'descripcion' => 'PELUQUERÍA',
            ],
            [
                'id'          => 64,
                'codigo'      => 'IMSE',
                'descripcion' => 'ESPECTACULOS EN VIVO',
            ],
            [
                'id'          => 65,
                'codigo'      => 'IMST',
                'descripcion' => 'PRODUCCIONES FOTOGRÁFICAS',
            ],
            [
                'id'          => 66,
                'codigo'      => 'IMSV',
                'descripcion' => 'PRODUCCIÓN AUDIOVISUAL',
            ],
            [
                'id'          => 67,
                'codigo'      => 'INAD',
                'descripcion' => 'ALIMENTOS DIVERSOS',
            ],
            [
                'id'          => 68,
                'codigo'      => 'INAE',
                'descripcion' => 'LÁCTEOS',
            ],
            [
                'id'          => 69,
                'codigo'      => 'INAF',
                'descripcion' => 'PANADERÍA, PASTELERÍA, CONFITERÍA Y MOLINERÍA',
            ],
            [
                'id'          => 70,
                'codigo'      => 'INAH',
                'descripcion' => 'BEBIDAS',
            ],
            [
                'id'          => 71,
                'codigo'      => 'INAI',
                'descripcion' => 'CÁRNICAS',
            ],
            [
                'id'          => 72,
                'codigo'      => 'INAJ',
                'descripcion' => 'PRODUCTOS DE LA PESCA',
            ],
            [
                'id'          => 73,
                'codigo'      => 'INAK',
                'descripcion' => 'ACEITES Y GRASAS',
            ],
            [
                'id'          => 74,
                'codigo'      => 'INAV',
                'descripcion' => 'CONSERVAS VEGETALES',
            ],
            [
                'id'          => 75,
                'codigo'      => 'MAMA',
                'descripcion' => 'TRANSFORMACIÓN MADERA Y CORCHO',
            ],
            [
                'id'          => 76,
                'codigo'      => 'MAMB',
                'descripcion' => 'INSTALACIÓN Y AMUEBLAMIENTO',
            ],
            [
                'id'          => 77,
                'codigo'      => 'MAMD',
                'descripcion' => 'PRODUCCION CARPINTERÍA Y MUEBLE',
            ],
            [
                'id'          => 78,
                'codigo'      => 'MAPB',
                'descripcion' => 'BUCEO',
            ],
            [
                'id'          => 79,
                'codigo'      => 'MAPN',
                'descripcion' => 'PESCA Y NAVEGACION',
            ],
            [
                'id'          => 80,
                'codigo'      => 'MAPU',
                'descripcion' => 'ACUICULTURA',
            ],
            [
                'id'          => 81,
                'codigo'      => 'QUIA',
                'descripcion' => 'ANÁLISIS Y CONTROL',
            ],
            [
                'id'          => 82,
                'codigo'      => 'QUIE',
                'descripcion' => 'PROCESO QUÍMICO',
            ],
            [
                'id'          => 83,
                'codigo'      => 'QUIM',
                'descripcion' => 'FARMAQUÍMICA',
            ],
            [
                'id'          => 84,
                'codigo'      => 'QUIO',
                'descripcion' => 'PASTA, PAPEL Y CARTÓN',
            ],
            [
                'id'          => 85,
                'codigo'      => 'QUIT',
                'descripcion' => 'TRANSFORMACIÓN DE POLÍMEROS',
            ],
            [
                'id'          => 86,
                'codigo'      => 'SANP',
                'descripcion' => 'SERVICIOS Y PRODUCTOS SANITARIOS',
            ],
            [
                'id'          => 87,
                'codigo'      => 'SANS',
                'descripcion' => 'SOPORTE Y AYUDA AL DIAGNÓSTICO',
            ],
            [
                'id'          => 88,
                'codigo'      => 'SANT',
                'descripcion' => 'ATENCIÓN SANITARIA',
            ],
            [
                'id'          => 89,
                'codigo'      => 'SEAD',
                'descripcion' => 'SEGURIDAD Y PREVENCIÓN',
            ],
            [
                'id'          => 90,
                'codigo'      => 'SEAG',
                'descripcion' => 'GESTIÓN AMBIENTAL',
            ],
            [
                'id'          => 91,
                'codigo'      => 'SSCB',
                'descripcion' => 'ACTIVIDADES CULTURALES Y RECREATIVAS',
            ],
            [
                'id'          => 92,
                'codigo'      => 'SSCE',
                'descripcion' => 'FORMACIÓN Y EDUCACIÓN',
            ],
            [
                'id'          => 93,
                'codigo'      => 'SSCG',
                'descripcion' => 'ATENCIÓN SOCIAL',
            ],
            [
                'id'          => 94,
                'codigo'      => 'SSCI',
                'descripcion' => 'SERVICIOS AL CONSUMIDOR',
            ],
            [
                'id'          => 95,
                'codigo'      => 'TCPC',
                'descripcion' => 'CALZADO',
            ],
            [
                'id'          => 96,
                'codigo'      => 'TCPF',
                'descripcion' => 'CONFECCIÓN EN TEXTIL Y PIEL',
            ],
            [
                'id'          => 97,
                'codigo'      => 'TCPN',
                'descripcion' => 'ENNOBLECIMIENTO DE MATERIAS TEXTILES Y PIELES',
            ],
            [
                'id'          => 98,
                'codigo'      => 'TCPP',
                'descripcion' => 'PRODUCCIÓN DE HILOS Y TEJIDOS',
            ],
            [
                'id'          => 99,
                'codigo'      => 'TMVB',
                'descripcion' => 'FERROCARRIL Y CABLE',
            ],
            [
                'id'          => 100,
                'codigo'      => 'TMVG',
                'descripcion' => 'ELECTROMECÁNICA DE VEHÍCULOS',
            ],
            [
                'id'          => 101,
                'codigo'      => 'TMVI',
                'descripcion' => 'CONDUCCION DE VEHÍCULOS POR CARRETERA',
            ],
            [
                'id'          => 102,
                'codigo'      => 'TMVL',
                'descripcion' => 'CARROCERÍA DE VEHÍCULOS',
            ],
            [
                'id'          => 103,
                'codigo'      => 'TMVO',
                'descripcion' => 'AERONÁUTICA',
            ],
            [
                'id'          => 104,
                'codigo'      => 'TMVU',
                'descripcion' => 'NÁUTICA',
            ],
            [
                'id'          => 105,
                'codigo'      => 'VICF',
                'descripcion' => 'FABRICACIÓN CERÁMICA',
            ],
            [
                'id'          => 106,
                'codigo'      => 'VICI',
                'descripcion' => 'VIDRIO INDUSTRIAL',
            ],
        ]);
    }
}
