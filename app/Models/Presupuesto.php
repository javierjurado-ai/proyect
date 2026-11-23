<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presupuesto extends Model
{
    protected $table = 'presupuesto';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    // 'objetivos' y 'contenidos' ya no están aquí: ahora son curso.objetivos / curso.contenido
    protected $fillable = [
        'numero_alumnos',
        'nombre_formacion',
        'horas_totales',
        'ubicacion_formacion',
        'importe_bonificado',
        'fecha_bonificado',
    ];

    protected $casts = [
        'numero_alumnos'     => 'integer',
        'horas_totales'      => 'float',
        'importe_bonificado' => 'decimal:2',
        'fecha_bonificado'   => 'date',
    ];

    // Un presupuesto puede tener uno o varios cursos asociados
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'presupuesto_id', 'id');
    }
}