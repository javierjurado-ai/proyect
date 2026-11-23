<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaProfesional extends Model
{
    protected $table = 'area_profesional';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
        'curso_id'
    ];

    protected $casts = [
    ];

    // Un aula pertenece a un curso
    public function curso(): BelongsTo  
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }
}
