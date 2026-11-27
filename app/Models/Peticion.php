<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peticion extends Model
{
    protected $table = 'peticion';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'peticion_mensajes_id',
        'peticiones_logistica_id',
        'titulo'
    ];

    protected $casts = [
    ];
}
