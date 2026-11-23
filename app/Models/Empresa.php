<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'total_trabajadores' => 'integer',
    ];

    // Una empresa tiene muchos centros
    public function centros(): HasMany
    {
        return $this->hasMany(Centro::class, 'empresa_id', 'id');
    }

    // Una empresa tiene muchos créditos formativos
    public function creditosFormativos(): HasMany
    {
        return $this->hasMany(CreditoFormativo::class, 'empresa_id', 'id');
    }

    // persona ya no tiene empresa_id: la empresa va en curso_alumno / curso_docente
    public function cursoAlumnos(): HasMany
    {
        return $this->hasMany(CursoAlumno::class, 'empresa_id', 'id');
    }

    public function cursoDocentes(): HasMany
    {
        return $this->hasMany(CursoDocente::class, 'empresa_id', 'id');
    }

    // Personas que han sido alumnos de esta empresa
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'curso_alumno', 'empresa_id', 'persona_id')->distinct();
    }

    // Cursos del grupo (tabla grupo_empresa)
    public function cursosGrupo(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'grupo_empresa', 'empresa_id', 'curso_id');
    }
}