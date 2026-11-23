<?php

namespace App\Models;

use App\Models\Enums\DiaSemana;
use App\Models\Enums\TurnoDocente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocenteCalendario extends Model
{
    protected $table = 'docente_calendario';
    protected $primaryKey = 'id';
    public $timestamps = false;

  
    protected $fillable = [
        'turno',
        'hora_inicio',
        'hora_fin',
        'dia_semana',
        'curso_docente_id',
    ];

    protected $casts = [
        'turno'      => TurnoDocente::class,
        'dia_semana' => DiaSemana::class,
    ];

    public function cursoDocente(): BelongsTo
    {
        return $this->belongsTo(CursoDocente::class, 'curso_docente_id', 'id');
    }
}