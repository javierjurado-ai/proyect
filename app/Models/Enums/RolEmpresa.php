<?php

namespace App\Models\Enums;

// rol.rol_empresa (se asigna a cada empresa en la tabla rol_empresa)
enum RolEmpresa: string
{
    case Formadora = 'FORMADORA';
    case Cliente   = 'CLIENTE';
    case Proveedor = 'PROVEEDOR';
}
