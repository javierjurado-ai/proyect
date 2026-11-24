<?php

namespace App\Models\Enums;

// Estados de peticiones_logistica
enum EstadoLogistica: string
{
    case Pendiente  = 'PENDIENTE';
    case EnProceso  = 'EN_PROCESO';
    case Enviada    = 'ENVIADA';
    case Entregada  = 'ENTREGADA';
    case Devuelta   = 'DEVUELTA';
    case Completada = 'COMPLETADA';
    case Cancelada  = 'CANCELADA';
}
