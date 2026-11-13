<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 
use App\Models\Enums\Estado;
class PeticionesLogistica extends Model
{
    protected $table = 'peticiones_logistica';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'estado' => Estado::class,
    ];

    /**
     * Una petición logística está asociada a uno o varios cursos.
     */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'peticiones_logistica_id', 'id');
    }

    /**
     * Una petición logística tiene muchos productos solicitados.
     */
    public function peticionProductos(): HasMany
    {
        return $this->hasMany(PeticionProducto::class, 'peticiones_logistica_id', 'id');
    }
}
