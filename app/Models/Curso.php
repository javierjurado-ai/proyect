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
        //eliminar cuando se elimine la columna curso.peticiones_logistica_id
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

    // Peticiones de material del curso (un curso puede tener varias)
    // OBSOLETO: lo usan CursoController y curso/visualizar.blade.php.
    // Quitar cuando se elimine curso.peticiones_logistica_id y se cambie a peticionesLogistica()
    public function peticionLogistica(): BelongsTo
    {
        return $this->belongsTo(PeticionesLogistica::class, 'peticiones_logistica_id', 'id');
    }

    public function peticionesLogistica(): HasMany
    {
        return $this->hasMany(PeticionesLogistica::class, 'curso_id', 'id');
    }

    public function movimientosAlmacen(): HasMany
    {
        return $this->hasMany(MovimientoAlmacen::class, 'curso_id', 'id');
    }

    // Equipos con nº de serie que tiene prestados ahora
    public function unidadesPrestadas(): HasMany
    {
        return $this->hasMany(ArticuloSerie::class, 'curso_id', 'id');
    }

    // Facturas emitidas a las empresas del curso
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'curso_id', 'id');
    }

    // Facturas de proveedores / docentes imputadas al curso
    public function facturasProveedora(): HasMany
    {
        return $this->hasMany(FacturaEmpresaProveedora::class, 'curso_id', 'id');
    }

    public function costes(): HasMany
    {
        return $this->hasMany(Coste::class, 'curso_id', 'id');
    }

    // Empresas del grupo (tabla grupo_empresa)
    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'grupo_empresa', 'curso_id', 'empresa_id');
    }
    public function calendario(): HasMany
{
    return $this->hasMany(CursoCalendario::class, 'curso_id', 'id');
}
}