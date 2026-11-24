<?php

namespace App\Models\Enums;

// factura.estado (facturas emitidas)
enum EstadoFactura: string
{
    case Borrador       = 'BORRADOR';
    case Emitida        = 'EMITIDA';
    case CobradaParcial = 'COBRADA_PARCIAL';
    case Cobrada        = 'COBRADA';
    case Rectificada    = 'RECTIFICADA';
}
