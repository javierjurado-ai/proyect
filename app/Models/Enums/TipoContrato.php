<?php

namespace App\Models\Enums;

enum TipoContrato: string
{
    case Indefinido     = 'INDEFINIDO';
    case Fijo_Discontinuo     = 'FIJO DISCONTINUO'; 
}