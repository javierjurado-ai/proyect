<?php

namespace App\Models\Enums;

enum TipoTutoria: string
{
    case CorreoElectronico = 'CORREO_ELECTRONICO';
    case Videoconferencia  = 'VIDEOCONFERENCIA';
    case Foro              = 'FORO';
    case Otras             = 'OTRAS';
}