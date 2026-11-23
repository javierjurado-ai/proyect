<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoAlumno extends Model
{
    protected $table = 'curso_alumno';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    // Todas estas columnas son NOT NULL en la BD.
    // Los campos SI/NO y los enums (nivel_estudio, categoria_profesional)
    // se guardan como texto con los valores exactos del ENUM de la BD.
    protected $fillable = [
        'curso_id',
        'persona_id',
        'empresa_id',
        'jornada_laboral',
        'certificado_afectado_terrorismo',
        'certificado_afectado_violencia_genero',
        'certificado_discapacidad',
        'nivel_estudio',
        'categoria_profesional',
        'erte',
        'fijo_discontinuo',
        'diploma',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }
}