<?php

namespace App\Models\Enums;

enum Tipo: string
{
    case Inicio = 'inicio';
    case Modificacion = 'modificacion';
    case Finalizacion  = 'finalizacion';
    case Cancelacion = 'cancelacion';
}