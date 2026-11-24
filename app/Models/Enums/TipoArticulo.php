<?php

namespace App\Models\Enums;

enum TipoArticulo: string
{
    case Equipo            = 'EQUIPO';
    case Fungible          = 'FUNGIBLE';
    case MaterialDidactico = 'MATERIAL_DIDACTICO';
    case Mobiliario        = 'MOBILIARIO';
    case Otro              = 'OTRO';
}
