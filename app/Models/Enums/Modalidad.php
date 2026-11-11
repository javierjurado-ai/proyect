<?php

namespace App\Models\Enums;

enum Modalidad: string
{
    case Presencial = 'presencial';
    case Teleformacion = 'teleformacion';
    case Mixta = 'mixta';
}