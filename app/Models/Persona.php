<?php

namespace App\Models;

use App\Models\enums\Genero;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    public function cursoAlumno(): HasMany
    {
        return $this->hasMany(curso_alumno::class, 'persona_idusuario', 'idusuario');
    }

     
    public function cursoDocentes(): HasMany
    {
        return $this->hasMany(curso_docente::class, 'persona_idusuario', 'idusuario');
    }

    
    public function cursosComoAlumno(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_alumno', 'persona_idusuario', 'grupo_idgrupo', 'idusuario', 'idgrupo');
    }

    
    public function cursosComoDocente(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_docente', 'persona_idusuario', 'grupo_idgrupo', 'idusuario', 'idgrupo');
    }
}