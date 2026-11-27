<?php

namespace App\Models\Enums;

Enum Rol: string
{
    case Alumno = 'ALUMNO';
    case Docente = 'DOCENTE';
    case Administrador = 'ADMINISTRADOR';
    case Comercial = 'COMERCIAL';
    case Coordinador = 'COORDINADOR';
}