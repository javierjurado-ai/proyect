<?php

namespace App\Models\Enums;

enum EstadoAula: string
{
    case Disponible = 'DISPONIBLE';
    case Ocupada = 'OCUPADA';
}