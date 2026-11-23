<?php

namespace App\Models;

use App\Models\Enums\TipoTutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocenteTutoria extends Model
{
    protected $table = 'docente_tutoria';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'tipo_tutoria',
        'descripcion',
        'curso_docente_id',
    ];

    protected $casts = [
        'tipo_tutoria' => TipoTutoria::class,
    ];

    public function cursoDocente(): BelongsTo
    {
        return $this->belongsTo(CursoDocente::class, 'curso_docente_id', 'id');
    }
}