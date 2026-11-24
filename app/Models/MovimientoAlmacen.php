<?php

namespace App\Models;

use App\Models\Enums\TipoMovimiento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovimientoAlmacen extends Model
{
    protected $table = 'movimiento_almacen';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'fecha',
        'almacen_origen_id',
        'almacen_destino_id',
        'curso_id',
        'peticiones_logistica_id',
        'proveedor_id',
        'factura_empresa_proveedora_id',
        'persona_id',
        'referencia',
        'observaciones',
    ];

    protected $casts = [
        'tipo'  => TipoMovimiento::class,
        'fecha' => 'datetime',
    ];

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id', 'id');
    }

    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id', 'id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    public function peticionLogistica(): BelongsTo
    {
        return $this->belongsTo(PeticionesLogistica::class, 'peticiones_logistica_id', 'id');
    }

    // Empresa con rol PROVEEDOR
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'proveedor_id', 'id');
    }

    public function facturaProveedora(): BelongsTo
    {
        return $this->belongsTo(FacturaEmpresaProveedora::class, 'factura_empresa_proveedora_id', 'id');
    }

    // Quién registró el movimiento
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(MovimientoAlmacenLinea::class, 'movimiento_almacen_id', 'id');
    }
}
