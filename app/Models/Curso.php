<?php
namespace App\Models;

use App\Models\Enums\Turno;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{

    protected $table = 'curso';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'formacion_id',
        'codigo_curso',
        'empresa_formadora',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_final',
        'turno',
        'aula',
        'aula_virtual',
        'tecnico_comunidad',
        'observaciones',
        'peticiones_logistica_id',
        'centro_id',
        'presupuesto_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'turno' => Turno::class
    ];

    // --- Relaciones ---

    // Relación: Un curso pertenece a una formación principal (formacion_id -> id)
    public function formacion()
    {
        return $this->belongsTo(Formacion::class, 'formacion_id', 'id');
    }

    // Relación: Un curso pertenece a un presupuesto (presupuesto_id -> id)
    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'presupuesto_id', 'id');
    }

    // Relación: Un curso pertenece a una petición logística (peticiones_logistica_id -> id)
    public function peticionLogistica()
    {
        return $this->belongsTo(PeticionesLogistica::class, 'peticiones_logistica_id', 'id');
    }

    // Relación: Un curso pertenece a un centro (centro_id -> id)
    public function centro()
    {
        return $this->belongsTo(Centro::class, 'centro_id', 'id');
    }

    // Relación: Un curso tiene muchas comunicaciones Fundae (curso_id -> id)
    public function comunicacionesFundae(): HasMany
    {
        return $this->hasMany(ComunicacionFundae::class, 'curso_id', 'id');
    }

    // Relación: Un curso tiene muchas asignaciones de grupo de empresa (curso_id -> id)
    public function gruposEmpresa(): HasMany
    {
        return $this->hasMany(GrupoEmpresa::class, 'curso_id', 'id');
    }

    // Relación: Un curso tiene muchos alumnos a través de la tabla pivote 'curso_alumno'
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(
            Persona::class, 
            'curso_alumno', 
            'curso_id', 
            'persona_id', 
            'id', 
            'id'
        );
    }

    // Relación: Un curso tiene muchos docentes a través de la tabla pivote 'curso_docente'
    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(
            Persona::class, 
            'curso_docente', 
            'curso_id', 
            'persona_id', 
            'id', 
            'id'
        );
    }

    // Relación de muchos a muchos con empresas a través de 'grupo_empresa'
    public function empresasGrupo(): BelongsToMany
    {
        return $this->belongsToMany(
            Empresa::class, 
            'grupo_empresa', 
            'curso_id', 
            'empresa_id', 
            'id', 
            'id'
        );
    }

}