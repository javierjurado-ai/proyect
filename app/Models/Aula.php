<?php

namespace App\Models;

use App\Models\enums\EstadoAula;
use Illuminate\Database\Eloquent\Model;
use App\Models\enums\UsoAula;
class Aula extends Model
{
    protected $table = 'aulas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre_aula',
        'capacidad',
        'estado',
        'uso_aula',
        'dimension',
        'observaciones',
        'curso_id'
    ];
    protected $casts = [
        'uso_aula' => UsoAula::class,
        'estado' => EstadoAula::class
    ];
}
