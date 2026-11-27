<?php

namespace App\Models\Enums;

enum Estado: string
{
    case Pendiente = 'PENDIENTE';
    case Completada = 'COMPLETADA';
}