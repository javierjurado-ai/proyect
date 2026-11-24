<?php

namespace App\Models;

use App\Models\Enums\EstadoFactura;
use App\Models\Enums\FormaPago;
use App\Models\Enums\TipoFactura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Facturas emitas a empresas clientes

class Factura extends Model 
{
    protected $table = 'factura';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'serie_factura_id',
        'numero',
        'tipo',
        'factura_rectificada_id',
        'motivo_rectificacion',
        'fecha_emision',
        'fecha_vencimiento',
        'empresa_id',
        'curso_id',
        'cliente_cif',
        'cliente_nombre',
        'cliente_direccion',
        'base_imponible',
        'total_iva',
        'total',
        'forma_pago',
        'estado',
        'hash',
        'hash_anterior',
        'persona_id',
        'observaciones',
    ];

    protected $casts = [
        'tipo'              => TipoFactura::class,
        'estado'            => EstadoFactura::class,
        'forma_pago'        => FormaPago::class,
        'numero'            => 'integer',
        'fecha_emision'     => 'date',
        'fecha_vencimiento' => 'date',
        'base_imponible'    => 'decimal:2',
        'total_iva'         => 'decimal:2',
        'total'             => 'decimal:2',
    ];

    public function serie(): BelongsTo
    {
        return $this->belongsTo(SerieFactura::class, 'serie_factura_id', 'id');
    }

    //Empresa con rol CLIENTE
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    // Quién la emite
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    public function facturaRectificada(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_rectificada_id', 'id');
    }

    public function rectificativas(): HasMany
    {
        return $this->hasMany(Factura::class, 'factura_rectificada_id', 'id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(FacturaLinea::class, 'factura_id', 'id');
    }

    public function cobros(): HasMany
    {
        return $this->hasMany(Cobro::class, 'factura_id', 'id');
    }


    // Número completo, p. ej. F-2026-00001 (null si es borrador): $factura->numero_completo
    public function getNumeroCompletoAttribute(): ?string
    {
        if ($this->numero === null || ! $this->serie) {
            return null;
        }

        return sprintf('%s-%d-%05d', $this->serie->codigo, $this->serie->anio, $this->numero);
    }

    public function getCobradoAttribute(): float
    {
        return (float) $this->cobros()->sum('importe');
    }

    public function getPendienteCobroAttribute(): float
    {
        return round((float) $this->total - $this->cobrado, 2);
    }
}