<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coste extends Model
{
    protected $table = 'coste';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Un solo registro por cada pareja curso + empresa (UNIQUE en la BD)
    protected $fillable = [
        'directos',
        'indirectos',
        'organizacion',
        'salariales',
        'curso_id',
        'empresa_id',
    ];

    protected $casts = [
        'directos'     => 'decimal:2',
        'indirectos'   => 'decimal:2',
        'organizacion' => 'decimal:2',
        'salariales'   => 'decimal:2',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    // Reparto del coste por meses
    public function periodos(): HasMany
    {
        return $this->hasMany(CursoCostePeriodo::class, 'coste_id', 'id');
    }

    // Suma de los cuatro conceptos: $coste->total
    public function getTotalAttribute(): float
    {
        return (float) $this->directos + (float) $this->indirectos + (float) $this->organizacion + (float) $this->salariales;
    }
}