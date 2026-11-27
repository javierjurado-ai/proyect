<?php

namespace App\Models\Enums;

// Tipo de trabajador del docente (columna curso_docente.catagoria).
// Los valores tienen que ser EXACTAMENTE los del enum de la BD: 'CUENTA AJENA' y 'AUTONOMO'.
enum Categoria: string
{
    case CuentaAjena = 'CUENTA AJENA';
    case Autonomo    = 'AUTONOMO';
}