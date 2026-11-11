<?php

namespace App\Models;

use App\Models\enums\Genero;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'idusuario';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'fecha_nacimiento',
        'genero',
        'nacionalidad',
        'telefono',
        'direccion',
        'email',
        'DNI',
        'NIE',
        'numero_seguridad_social',
        'LOPD',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'genero'           => Genero::class,
    ];
}