<?php

namespace App\Models\Enums;

enum DiaSemana: string
{
    case Lunes     = 'LUNES';
    case Martes    = 'MARTES';
    case Miercoles = 'MIERCOLES';
    case Jueves    = 'JUEVES';
    case Viernes   = 'VIERNES';
    case Sabado    = 'SABADO';
    case Domingo   = 'DOMINGO';
}