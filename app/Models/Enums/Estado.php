<?php

namespace App\Models\enums;

enum Estado: string
{
    case Pendiente = 'PENDIENTE';
    case Completada = 'COMPLETADA';
}