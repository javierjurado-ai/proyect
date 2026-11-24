<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoAlmacenLinea extends Model
{
    protected $table = 'movimiento_almacen_linea';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // cantidad siempre positiva: el tipo del movimiento dice si suma o resta
    protected $fillable = [
        'movimiento_almacen_id',
        'articulo_id',
        'articulo_serie_id',
        'cantidad',
        'coste_unitario',
    ];

    protected $casts = [
        'cantidad'       => 'integer',
        'coste_unitario' => 'decimal:2',
    ];

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(MovimientoAlmacen::class, 'movimiento_almacen_id', 'id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id', 'id');
    }

    // Obligatoria si el artículo controla nº de serie
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(ArticuloSerie::class, 'articulo_serie_id', 'id');
    }
}
