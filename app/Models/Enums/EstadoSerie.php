<?php

namespace App\Models\Enums;

// articulo_serie.estado
enum EstadoSerie: string
{
    case Disponible   = 'DISPONIBLE';
    case Prestado     = 'PRESTADO';
    case EnReparacion = 'EN_REPARACION';
    case Baja         = 'BAJA';
}
