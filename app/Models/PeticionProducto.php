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
        'producto',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    // Antes se llamaba curso(), pero apunta a la petición logística
    public function peticionLogistica(): BelongsTo
    {
        return $this->belongsTo(PeticionesLogistica::class, 'peticiones_logistica_id', 'id');
    }
}