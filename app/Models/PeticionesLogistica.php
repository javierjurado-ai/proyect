<?php

namespace App\Models;

use App\Models\Enums\EstadoLogistica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeticionesLogistica extends Model
{
    protected $table = 'peticiones_logistica';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'curso_id',
        'solicitante_id',
        'almacen_id',
        'centro_id',
        'fecha',
        'fecha_necesaria',
        'fecha_devolucion_prevista',
        'estado',
        'observaciones',
        'direccion_envio',
        'transportista',
        'seguimiento',
        'coste_envio',
    ];

    protected $casts = [
        'fecha'                     => 'date',
        'fecha_necesaria'           => 'date',
        'fecha_devolucion_prevista' => 'date',
        'estado'                    => EstadoLogistica::class,
        'coste_envio'               => 'decimal:2',
    ];

    // Curso para el que se pide el material
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    // Quién pide el material
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'solicitante_id', 'id');
    }

    // De dónde sale el material
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id', 'id');
    }

    // A dónde va
    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centro::class, 'centro_id', 'id');
    }

    // Una petición logística tiene muchos productos solicitados
    public function peticionProductos(): HasMany
    {
        return $this->hasMany(PeticionProducto::class, 'peticiones_logistica_id', 'id');
    }

    // Salidas y devoluciones de almacén de esta petición
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoAlmacen::class, 'peticiones_logistica_id', 'id');
    }

    // OBSOLETO: relación antigua por curso.peticiones_logistica_id. Usa curso().
    // Quitar cuando se elimine esa columna de la BD.
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'peticiones_logistica_id', 'id');
    }
}
