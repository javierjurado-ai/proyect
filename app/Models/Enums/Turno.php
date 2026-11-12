<?php

namespace App\Models\Enums;

enum Turno: string
{
    case Manana = 'MAÑANA';
    case Tarde = 'TARDE';
    case Partido = 'PARTIDO';
}