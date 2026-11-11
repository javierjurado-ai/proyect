<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    protected $fillable = [
        'cif', 
        'nombre', 
        'total_trabajadores',
    ];

    protected $casts = [
    ];
}
