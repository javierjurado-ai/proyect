<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'peticiones_logistica';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'rol',
        'persona_id'
    ];

    protected $casts = [
        'rol' => Rol::class
    ];
}
