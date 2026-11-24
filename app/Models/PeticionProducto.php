<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeticionProducto extends Model
{
    protected $table = 'peticion_producto';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'peticiones_logistica_id',
        'articulo_id',
        // OBSOLETO: texto libre antiguo. Se rellena solo con el nombre del artículo
        // (ver booted). Quitar cuando se elimine la columna de la BD.
        'producto',
        'cantidad',
        'cantidad_servida',
        'cantidad_devuelta',
    ];

    protected $casts = [
        'cantidad'          => 'integer',
        'cantidad_servida'  => 'integer',
        'cantidad_devuelta' => 'integer',
    ];

    // La columna 'producto' sigue siendo NOT NULL en la BD: mientras exista,
    // se copia el nombre del artículo para no tener que enviarla.
    protected static function booted(): void
    {
        static::saving(function (PeticionProducto $linea) {
            if (empty($linea->producto) && $linea->articulo_id) {
                $linea->producto = Articulo::whereKey($linea->articulo_id)->value('nombre');
            }
        });
    }

    public function peticionLogistica(): BelongsTo
    {
        return $this->belongsTo(PeticionesLogistica::class, 'peticiones_logistica_id', 'id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id', 'id');
    }

    // Lo que sigue fuera del almacén: $linea->pendiente_devolucion
    public function getPendienteDevolucionAttribute(): int
    {
        return max(0, $this->cantidad_servida - $this->cantidad_devuelta);
    }
}
