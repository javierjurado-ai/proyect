<?php

namespace App\Models\enums;

enum Estado: string
{
    case Pendiente = 'Pendiente';
    case Completada = 'Completada';
}