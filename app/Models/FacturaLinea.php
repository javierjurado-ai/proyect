<?php

namespace App\Models;

use App\Models\Enums\LOPD;
use App\Models\Enums\TipoCoste;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaLinea extends Model
{
    protected $table = 'factura_linea';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'factura_id',
        'concepto',
        'tipo_coste',
        'curso_id',
        'articulo_id',
        'cantidad',
        'precio_unitario',
        'descuento_pct',
        'tipo_iva',
        'exento',
        'motivo_exencion',
        'base',
        'cuota_iva',
    ];

    // Valores por defecto de la BD, para que el cálculo de base/cuota los tenga
    protected $attributes = [
        'cantidad'      => 1,
        'descuento_pct' => 0,
        'tipo_iva'      => 21,
        'exento'        => 'NO',
    ];

    protected $casts = [
        'tipo_coste'      => TipoCoste::class,
        'exento'          => LOPD::class,
        'cantidad'        => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'descuento_pct'   => 'decimal:2',
        'tipo_iva'        => 'decimal:2',
        'base'            => 'decimal:2',
        'cuota_iva'       => 'decimal:2',
    ];

    // Calcula base y cuota a partir de cantidad, precio, descuento e IVA

    protected static function booted(): void
    {
        static::saving(function (FacturaLinea $linea) {
            $base = (float) $linea->cantidad * (float) $linea->precio_unitario
                  * (1 - (float) $linea->descuento_pct / 100);
            $linea->base = round($base, 2);
            $linea->cuota_iva = $linea->exento === LOPD::Si
                ? 0
                : round($base * (float) $linea->tipo_iva / 100, 2);
        });
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id', 'id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id', 'id');
    }

    
}