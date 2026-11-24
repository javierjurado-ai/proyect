<?php

namespace App\Models\Enums;

// movimiento_almacen.tipo
enum TipoMovimiento: string
{
    case EntradaCompra   = 'ENTRADA_COMPRA';
    case SalidaCurso     = 'SALIDA_CURSO';
    case DevolucionCurso = 'DEVOLUCION_CURSO';
    case Traspaso        = 'TRASPASO';
    case Ajuste          = 'AJUSTE';
    case Baja            = 'BAJA';
}
