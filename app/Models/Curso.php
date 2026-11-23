<?php

namespace App\Models;

use App\Models\Enums\Modalidad;
use App\Models\Enums\Turno;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $table = 'curso';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Ya no existen 'codigo_curso' ni 'aula_virtual' (ahora tabla curso_aula_virtual)
    protected $fillable = [
        'codigo_accion',
        'codigo_grupo',
        'empresa_formadora',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_final',
        'turno',
        'aula',
        'tecnico_comunidad',
        'observaciones',
        'objetivos',
        'contenido',
        'horas_presenciales',
        'horas_teleformacion',
        'modalidad',
        'nivel_formacion',
        'centro_id',
        'presupuesto_id',
        'formacion_id',
        'peticiones_logistica_id',
    ];

    protected $casts = [
        'fecha_inicio'        => 'date',
        'fecha_fin'           => 'date',
        'turno'               => Turno::class,
        'modalidad'           => Modalidad::class,
        'horas_presenciales'  => 'decimal:2',
        'horas_teleformacion' => 'decimal:2',
        'nivel_formacion'     => 'integer',
    ];

    // --- Relaciones "pertenece a" ---

    public function formacion(): BelongsTo
    {
        return $this->belongsTo(Formacion::class, 'formacion_id', 'id');
    }

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class, 'presupuesto_id', 'id');
    }

    public function peticionLogistica(): BelongsTo
    {
        return $this->belongsTo(PeticionesLogistica::class, 'peticiones_logistica_id', 'id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centro::class, 'centro_id', 'id');
    }

    // --- Relaciones "tiene muchos" ---

    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'curso_id', 'id');
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(CursoAlumno::class, 'curso_id', 'id');
    }

    public function docentes(): HasMany
    {
        return $this->hasMany(CursoDocente::class, 'curso_id', 'id');
    }

    public function comunicacionesFundae(): HasMany
    {
        return $this->hasMany(ComunicacionFundae::class, 'curso_id', 'id');
    }

    public function contactosCentro(): HasMany
    {
        return $this->hasMany(CentroContactos::class, 'curso_id', 'id');
    }

    // Empresas del grupo (tabla grupo_empresa)
    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'grupo_empresa', 'curso_id', 'empresa_id');
    }
}