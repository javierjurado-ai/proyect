<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\Modalidad;
use Illuminate\Database\Seeder;
class Formacion extends Model
{
     protected $table = 'formacion';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    protected $fillable = [
        'nombre', 
        'codigo_accion', 
        'modalidad', 
        'sector', 
        'horas_totales', 
        'activo',
    ];

    protected $casts = [
        'modalidad' => Modalidad::class,
    ];
}
