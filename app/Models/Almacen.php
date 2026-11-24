<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Almacen extends Model
{
    protected $table = 'almacen';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable =[
        'nombre',
        'direccion',
        'municipio',
        'provincia',
        'centro_id',
        'responsable_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centro::class, 'centro_id', 'id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'responsable_id', 'id');
    }

    // Cantidad de cada artículo en este almacén
    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class, 'almacen_id', 'id');
    }

    // Unidades con nº de serie que están ahora en este almacén
    public function unidades(): HasMany
    {
        return $this->hasMany(ArticuloSerie::class, 'almacen_id', 'id');
    }

    public function movimientosSalida(): HasMany
    {
        return $this->hasMany(MovimientoAlmacen::class, 'almacen_origen_id', 'id');
    }

    public function movimientosEntrada(): HasMany
    {
        return $this->hasMany(MovimientoAlmacen::class, 'almacen_destino_id', 'id');
    }

    public function peticionesLogistica(): HasMany
    {
        return $this->hasMany(PeticionesLogistica::class, 'almacen_id', 'id');
    }
}