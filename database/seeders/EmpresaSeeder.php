<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\AyudasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Empresas de prueba y sus roles (FORMADORA, CLIENTE, PROVEEDOR).
 * La clave (AULA, TRANS, HOTEL…) es solo para que los demás seeders las encuentren.
 */
class EmpresaSeeder extends Seeder
{
    use AyudasSeeder;

    public const EMPRESAS = [
        // --- Formadoras ---
        'AULA' => ['letra' => 'B', 'num' => 8642117, 'nombre' => 'Aula Activa Formación S.L.', 'roles' => ['FORMADORA'],
            'direccion' => 'Calle de Bravo Murillo 145', 'cp' => '28020', 'municipio' => 'Madrid', 'provincia' => 'Madrid',
            'email' => 'administracion@aulaactiva.test', 'telefono' => '910456780', 'trabajadores' => 22, 'vencimiento' => 30],
        'ITL' => ['letra' => 'B', 'num' => 9731544, 'nombre' => 'Instituto Técnico Levante S.L.', 'roles' => ['FORMADORA'],
            'direccion' => 'Avenida del Puerto 212', 'cp' => '46023', 'municipio' => 'Valencia', 'provincia' => 'Valencia',
            'email' => 'facturas@itlevante.test', 'telefono' => '963221450', 'trabajadores' => 15, 'vencimiento' => 30],

        // --- Clientes ---
        'TRANS' => ['letra' => 'B', 'num' => 8213590, 'nombre' => 'Transportes Hermanos Navarro S.L.', 'roles' => ['CLIENTE'],
            'direccion' => 'Calle de la Industria 18, Polígono Las Monjas', 'cp' => '28850', 'municipio' => 'Torrejón de Ardoz', 'provincia' => 'Madrid',
            'email' => 'contabilidad@transportesnavarro.test', 'telefono' => '916750312', 'trabajadores' => 85, 'vencimiento' => 30,
            'dominio' => 'transportesnavarro.test'],
        'DENT' => ['letra' => 'B', 'num' => 8574426, 'nombre' => 'Clínica Dental Sonrisa S.L.', 'roles' => ['CLIENTE'],
            'direccion' => 'Avenida de España 32, local 4', 'cp' => '28100', 'municipio' => 'Alcobendas', 'provincia' => 'Madrid',
            'email' => 'gerencia@clinicasonrisa.test', 'telefono' => '916612087', 'trabajadores' => 12, 'vencimiento' => 15,
            'dominio' => 'clinicasonrisa.test'],
        'HOTEL' => ['letra' => 'A', 'num' => 4118263, 'nombre' => 'Hotel Mirador del Sur S.A.', 'roles' => ['CLIENTE'],
            'direccion' => 'Paseo de las Delicias 9', 'cp' => '41013', 'municipio' => 'Sevilla', 'provincia' => 'Sevilla',
            'email' => 'administracion@hotelmiradorsur.test', 'telefono' => '954238100', 'trabajadores' => 140, 'vencimiento' => 45,
            'dominio' => 'hotelmiradorsur.test'],
        'CONS' => ['letra' => 'B', 'num' => 5039871, 'nombre' => 'Construcciones Ebro Norte S.L.', 'roles' => ['CLIENTE'],
            'direccion' => 'Calle Alcalde Caballero 41', 'cp' => '50014', 'municipio' => 'Zaragoza', 'provincia' => 'Zaragoza',
            'email' => 'rrhh@ebronorte.test', 'telefono' => '976470215', 'trabajadores' => 60, 'vencimiento' => 60,
            'dominio' => 'ebronorte.test'],
        'SUPER' => ['letra' => 'A', 'num' => 4627718, 'nombre' => 'Supermercados La Huerta S.A.', 'roles' => ['CLIENTE'],
            'direccion' => 'Carrer de Sant Vicent Màrtir 250', 'cp' => '46017', 'municipio' => 'Valencia', 'provincia' => 'Valencia',
            'email' => 'proveedores@superlahuerta.test', 'telefono' => '963781540', 'trabajadores' => 320, 'vencimiento' => 30,
            'dominio' => 'superlahuerta.test'],
        'TALL' => ['letra' => 'B', 'num' => 8890152, 'nombre' => 'Talleres Mecánicos Ruiz S.L.', 'roles' => ['CLIENTE'],
            'direccion' => 'Calle de la Fundición 7', 'cp' => '28906', 'municipio' => 'Getafe', 'provincia' => 'Madrid',
            'email' => 'taller@talleresruiz.test', 'telefono' => '916954410', 'trabajadores' => 18, 'vencimiento' => 30,
            'dominio' => 'talleresruiz.test'],
        'ASES' => ['letra' => 'B', 'num' => 8761305, 'nombre' => 'Martín & Asociados Asesores S.L.P.', 'roles' => ['CLIENTE'],
            'direccion' => 'Calle de Serrano 88, 3º izda.', 'cp' => '28006', 'municipio' => 'Madrid', 'provincia' => 'Madrid',
            'email' => 'oficina@martinasociados.test', 'telefono' => '914357762', 'trabajadores' => 9, 'vencimiento' => 15,
            'dominio' => 'martinasociados.test'],
        'PAN' => ['letra' => 'B', 'num' => 4512096, 'nombre' => 'Panificadora Castellana S.L.', 'roles' => ['CLIENTE'],
            'direccion' => 'Carretera de Ávila km 3', 'cp' => '45005', 'municipio' => 'Toledo', 'provincia' => 'Toledo',
            'email' => 'admin@panificadoracastellana.test', 'telefono' => '925283371', 'trabajadores' => 45, 'vencimiento' => 30,
            'dominio' => 'panificadoracastellana.test'],
        // Cliente dado de baja: sirve para probar filtros de "activo"
        'GEST' => ['letra' => 'B', 'num' => 8102275, 'nombre' => 'Gestoría Antigua S.L.', 'roles' => ['CLIENTE'], 'activo' => false,
            'direccion' => 'Calle Mayor 3', 'cp' => '28013', 'municipio' => 'Madrid', 'provincia' => 'Madrid',
            'email' => null, 'telefono' => null, 'trabajadores' => 5, 'vencimiento' => 30],

        // --- Proveedores ---
        'INFO' => ['letra' => 'B', 'num' => 8325419, 'nombre' => 'Suministros Informáticos Centro S.L.', 'roles' => ['PROVEEDOR'],
            'direccion' => 'Calle de Embajadores 190', 'cp' => '28045', 'municipio' => 'Madrid', 'provincia' => 'Madrid',
            'email' => 'ventas@sicentro.test', 'telefono' => '915270644', 'trabajadores' => null, 'vencimiento' => 30],
        'PAPEL' => ['letra' => 'B', 'num' => 8456623, 'nombre' => 'Papelería Técnica Madrid S.L.', 'roles' => ['PROVEEDOR'],
            'direccion' => 'Calle de Fuencarral 51', 'cp' => '28004', 'municipio' => 'Madrid', 'provincia' => 'Madrid',
            'email' => 'pedidos@papeleriatecnica.test', 'telefono' => '915214398', 'trabajadores' => null, 'vencimiento' => 30],
        'EQUIP' => ['letra' => 'A', 'num' => 8197330, 'nombre' => 'Equipamientos de Oficina Prado S.A.', 'roles' => ['PROVEEDOR'],
            'direccion' => 'Avenida de la Recomba 12', 'cp' => '28914', 'municipio' => 'Leganés', 'provincia' => 'Madrid',
            'email' => 'comercial@equipamientosprado.test', 'telefono' => '916882215', 'trabajadores' => null, 'vencimiento' => 60],
    ];

    /** CIF con su carácter de control (para sociedades A/B es un dígito). */
    public static function cif(string $clave): string
    {
        $e = self::EMPRESAS[$clave];
        $cifras = sprintf('%07d', $e['num']);

        $suma = 0;
        foreach (str_split($cifras) as $i => $c) {
            $c = (int) $c;
            if ($i % 2 === 0) {                 // posiciones impares (1ª, 3ª…): se duplica y se suman sus cifras
                $c *= 2;
                $c = intdiv($c, 10) + $c % 10;
            }
            $suma += $c;
        }

        return $e['letra'] . $cifras . ((10 - $suma % 10) % 10);
    }

    public function run(): void
    {
        foreach (self::EMPRESAS as $clave => $e) {
            $id = $this->insertar('empresa', [
                'cif'                => self::cif($clave),
                'nombre'             => $e['nombre'],
                'direccion'          => $e['direccion'],
                'codigo_postal'      => $e['cp'],
                'municipio'          => $e['municipio'],
                'provincia'          => $e['provincia'],
                'email_facturacion'  => $e['email'],
                'dias_vencimiento'   => $e['vencimiento'],
                'telefono'           => $e['telefono'],
                'total_trabajadores' => $e['trabajadores'],
                'activo'             => $e['activo'] ?? true,
            ]);

            foreach ($e['roles'] as $rol) {
                DB::table('rol_empresa')->insert([
                    'empresa_id' => $id,
                    'rol_id'     => $this->idDe('rol', ['rol_empresa' => $rol]),
                ]);
            }
        }
    }
}
