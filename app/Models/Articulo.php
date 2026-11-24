<?php

namespace App\Models;

use App\Models\Enums\LOPD;
use App\Models\Enums\TipoArticulo;
use App\Models\Enums\UnidadArticulo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Articulo extends Model
{
    protected $table = 'articulo';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'unidad',
        'retornable',
        'controla_serie',
        'precio_coste',
        'precio_venta',
        'tipo_iva',
        'stock_minimo',
        'proveedor_habitual_id',
        'activo',
    ];

    protected $casts = [
        'tipo'           => TipoArticulo::class,
        'unidad'         => UnidadArticulo::class,
        'retornable'     => LOPD::class,
        'controla_serie' => LOPD::class,
        'precio_coste'   => 'decimal:2',
        'precio_venta'   => 'decimal:2',
        'tipo_iva'       => 'decimal:2',
        'stock_minimo'   => 'integer',
        'activo'         => 'boolean',
    ];

    // Empresa con rol PROVEEDOR

    public function proveedorHabitual(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'proveedor_habitual_id', 'id');
    }

    public function unidades(): HasMany
    {
        return $this->hasMany(ArticuloSerie::class, 'articulo_id', 'id');
    }

    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class, 'articulo_id', 'id');
    }

    public function lineasMovimiento(): HasMany
    {
        return $this->hasMany(MovimientoAlmacenLinea::class, 'articulo_id', 'id');
    }

    public function lineasPeticion(): HasMany
    {
        return $this->hasMany(PeticionProducto::class, 'articulo_id', 'id');
    }

    // stock total sumando todos los almacenes
    public function getStockTotalAttribute(): int
    {
        return (int) $this->stock()->sum('cantidad');
    }
    
}