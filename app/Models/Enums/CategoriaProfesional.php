<?php

namespace App\Models\Enums;

enum CategoriaProfesional: string
{
    case Directivo                   = 'DIRECTIVO';
    case MandoIntermedio             = 'MANDO_INTERMEDIO';
    case Tecnico                     = 'TECNICO';
    case TrabajadorCualificado       = 'TRABAJADOR_CUALIFICADO';
    case TrabajadorBajaCualificacion = 'TRABAJADOR_BAJA_CUALIFICACION';
}