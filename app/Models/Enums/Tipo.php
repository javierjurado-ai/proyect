<?php

namespace App\Models\Enums;

enum Tipo: string
{
    case Inicio = 'INICIO';
    case Modificacion = 'MODIFICACION';
    case Finalizacion  = 'FINALIZACION';
    case Cancelacion = 'CANCELACION';
}