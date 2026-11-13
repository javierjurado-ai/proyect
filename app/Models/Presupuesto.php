<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    
    protected $table = 'presupuesto';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

   
    public $timestamps = false;

    protected $fillable = [
        'numero_alumnos',
        'nombre_formacion',
        'horas_totales',
        'ubicacion_formacion',
        'objetivos',
        'contenidos',
        'importe_bonificado',
        'mes_bonificado',
    ];

    /**
     * Un presupuesto puede tener uno o varios cursos asociados.
     */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'presupuesto_id', 'id');
    }
}