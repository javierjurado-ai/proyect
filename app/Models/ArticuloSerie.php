<?php

namespace App\Models;
use App\Models\Enums\EstadoSerie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticuloSerie extends Model
{
    protected $table = 'articulo_serie';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'articulo_id',
        'numero_serie',
        'almacen_id',
        'curso_id',
        'estado',
        'fecha_alta',
    ];

    protected $casts = [
        'estado'     => EstadoSerie::class,
        'fecha_alta' => 'date',
    ];

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id', 'id');
    }

    // NULL cuando la unidad está fuera, prestada a un curso
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id', 'id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    public function lineasMovimiento(): HasMany
    {
        return $this->hasMany(MovimientoAlmacenLinea::class, 'articulo_serie_id', 'id');
    }

    
    
}