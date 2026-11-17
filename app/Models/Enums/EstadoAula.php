<?php

namespace App\Models\enums;

enum EstadoAula: string
{
    case Disponible = 'DISPONIBLE';
    case Ocupada = 'OCUPADA';
    
}