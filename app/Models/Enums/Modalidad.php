<?php

namespace App\Models\Enums;

enum Modalidad: string
{
    case Presencial = 'PRESENCIAL';
    case Teleformacion = 'TELEFORMACION';
    case Mixta = 'MIXTA';
}