<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $table = 'stock';
    public $timestamps = false;

    
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'almacen_id',
        'articulo_id',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    // Para que save()/update() filtren por las dos columnas de la clave
    protected function setKeysForSaveQuery($query): Builder
    {
        return $query->where('almacen_id', $this->getOriginal('almacen_id', $this->almacen_id))
                     ->where('articulo_id', $this->getOriginal('articulo_id', $this->articulo_id));
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id', 'id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id', 'id');
    }
}
