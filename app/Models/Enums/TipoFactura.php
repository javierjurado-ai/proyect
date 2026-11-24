<?php

namespace App\Models\Enums;

enum TipoFactura: string
{
    case Ordinaria     = 'ORDINARIA';
    case Rectificativa = 'RECTIFICATIVA';
}
