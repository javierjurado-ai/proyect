<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formacion extends Model
{
    protected $table = 'formacion';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    // En la BD ya no existen 'modalidad' ni 'sector' (la modalidad está en curso)
    protected $fillable = [
        'nombre',
        'codigo_accion',
        'horas_totales',
        'activo',
    ];

    protected $casts = [
        'codigo_accion' => 'integer',
        'horas_totales' => 'float',
        'activo'        => 'boolean',
    ];

    // Una formación tiene muchos cursos
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'formacion_id', 'id');
    }
}