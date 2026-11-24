<?php

namespace App\Models\Enums;

enum FormaPago: string
{
    case Transferencia = 'TRANSFERENCIA';
    case Domiciliacion = 'DOMICILIACION';
    case Tarjeta       = 'TARJETA';
    case Efectivo      = 'EFECTIVO';
}
