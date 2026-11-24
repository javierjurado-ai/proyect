<?php

namespace App\Models\Enums;

enum NivelEstudio: string
{
    case MenosPrimaria     = 'MENOS_PRIMARIA';
    case Primaria     = 'PRIMARIA';
    case EsoEgb = 'ESO_EGB';
    case BachilleratoFPMedio = 'BACHILLERATO_FP_MEDIO';
    case CP_Nivel_3     = 'CP_NIVEL_3';
    case FP_Superior = 'FP_SUPERIOR';
    case Grado_Diplomatura = 'GRADO_DIPLOMATURA';
    case Licenciatura_Master = 'LICENCIATURA_MASTER';
    case Doctorado = 'DOCTORADO';
    case Otras = 'OTRAS';
}