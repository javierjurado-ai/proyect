<?php

namespace App\Models\Enums;

enum ClaseRetribucion: string
{
    case PrecioHora     = 'PRECIO HORA';
    case PrecioFijo     = 'PRECIO FIJO';
    case BrutoMensual12 = 'BRUTO MENSUAL 12';
    case BrutoMensual14 = 'BRUTO MENSUAL 14';
    case BrutoAnual     = 'BRUTO ANUAL';
}