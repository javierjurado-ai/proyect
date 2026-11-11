<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeticionProducto extends Model
{
    protected $table = 'peticion_producto';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'peticiones_logistica_id',
        'producto',
        'cantidad'
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(
            PeticionesLogistica::class,
            'peticiones_logistica_id',
            'id'
        );
    }
}
