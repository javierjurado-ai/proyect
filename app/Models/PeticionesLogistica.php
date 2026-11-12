<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 
use App\Models\Enums\Estado;
class PeticionesLogistica extends Model
{
    protected $table = 'peticiones_logistica';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'estado' => Estado::class,
    ];
}
