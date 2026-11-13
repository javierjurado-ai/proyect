<?php

namespace App\Models;

use App\Models\enums\Genero;
use App\Models\enums\LOPD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'id';
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
        'LOPD'             => LOPD::class
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    public function cursoAlumno(): HasMany
    {
        return $this->hasMany(CursoAlumno::class, 'persona_id', 'id');
    }


    public function cursoDocentes(): HasMany
    {
        return $this->hasMany(CursoDocente::class, 'persona_id', 'id');
    }


    public function cursosComoAlumno(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_alumno', 'persona_id', 'curso_id', 'id', 'id');
    }


    public function cursosComoDocente(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_docente', 'persona_id', 'curso_id', 'id', 'id');
    }
}
