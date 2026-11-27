<?php

namespace App\Models\Enums;

enum TipoContrato: string
{
    case Indefinido      = 'INDEFINIDO';
    case FijoDiscontinuo = 'FIJO DISCONTINUO';
    case ObraYServicio   = 'OBRA Y SERVICIO';
    case TiempoCompleto  = 'TIEMPO COMPLETO';
}