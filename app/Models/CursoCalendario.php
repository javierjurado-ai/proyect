<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoCalendario extends Model
{
    protected $table = 'curso_calendario';
    protected $primaryKey = 'id';
    public $timestamps = false;

    
    protected $fillable = [
        'curso_id',
        'fecha',
        'hora_inicio1',
        'hora_fin1',
        'hora_inicio2',
        'hora_fin2',
    ];

    protected $casts = [
        'fecha' => 'date',
        
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }
}