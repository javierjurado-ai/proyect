<?php

namespace App\Models\Enums;

// tipo_coste de las líneas de factura
enum TipoCoste: string
{
    case Directo      = 'DIRECTO';
    case Indirecto    = 'INDIRECTO';
    case Organizacion = 'ORGANIZACION';
    case Otro         = 'OTRO';
}
