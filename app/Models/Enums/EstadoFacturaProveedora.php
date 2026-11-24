<?php

namespace App\Models\Enums;

// factura_empresa_proveedora.estado
enum EstadoFacturaProveedora: string
{
    case Pendiente = 'PENDIENTE';
    case Pagada    = 'PAGADA';
    case Anulada   = 'ANULADA';
}
