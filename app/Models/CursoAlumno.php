<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoAlumno extends Model
{
    protected $table = 'curso_alumno';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'grupo_idgrupo',
        'persona_idusuario',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_idusuario', 'idusuario');
    }

   public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'grupo_idgrupo', 'idgrupo');
    }
    
}