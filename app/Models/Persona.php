<?php

namespace App\Models;

use App\Models\Enums\Genero;
use App\Models\Enums\LOPD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    // 'empresa_id' ya no existe en persona: la empresa va en curso_alumno / curso_docente
    protected $fillable = [
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
        'LOPD'             => LOPD::class,
    ];

    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'persona_id', 'id');
    }

    // Roles de la persona (tabla intermedia rol_persona)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_persona', 'persona_id', 'rol_id');
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
        return $this->belongsToMany(Curso::class, 'curso_alumno', 'persona_id', 'curso_id');
    }

    public function cursosComoDocente(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_docente', 'persona_id', 'curso_id');
    }

    // Empresas con las que ha participado como alumno
    public function empresasComoAlumno(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'curso_alumno', 'persona_id', 'empresa_id')->distinct();
    }

    // Facturas que emite como docente autónomo
    public function facturasComoDocente(): HasMany
    {
        return $this->hasMany(FacturaEmpresaProveedora::class, 'docente_id', 'id');
    }

    // Peticiones de material que ha hecho
    public function peticionesLogistica(): HasMany
    {
        return $this->hasMany(PeticionesLogistica::class, 'solicitante_id', 'id');
    }
}
