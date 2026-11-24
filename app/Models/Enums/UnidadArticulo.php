<?php

namespace App\Models\Enums;

enum UnidadArticulo: string
{
    case Unidad   = 'UNIDAD';
    case Caja     = 'CAJA';
    case Paquete  = 'PAQUETE';
    case Licencia = 'LICENCIA';
}
