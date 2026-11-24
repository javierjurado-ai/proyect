<?php

namespace App\Models\Enums;

// empresa_rol.rol
enum RolEmpresa: string
{
    case Formadora = 'FORMADORA';
    case Cliente   = 'CLIENTE';
    case Proveedor = 'PROVEEDOR';
}
