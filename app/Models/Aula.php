<?php

namespace App\Models;

use App\Models\Enums\EstadoAula;
use App\Models\Enums\UsoAula;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aula extends Model
{
    protected $table = 'aulas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre_aula',
        'centro_id',
        'capacidad',
        'estado',
        'uso_aula',
        'dimension',
        'observaciones',
        'curso_id',
    ];

    protected $casts = [
        'estado'   => EstadoAula::class,
        'uso_aula' => UsoAula::class,
    ];

    // Un aula pertenece a un curso
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }
}