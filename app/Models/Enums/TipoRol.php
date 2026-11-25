<?php

namespace App\Models\Enums;

enum TipoRol: string
{
    case Alumno = 'ALUMNO';
    case Docente = 'DOCENTE';
    case Administrador = 'ADMINISTRADOR';
    case Comercial = 'COMERCIAL';
    case Coordinador = 'COORDINADOR';
    case Logistica = 'LOGISTA';
}