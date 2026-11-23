<?php

namespace App\Models;

use App\Models\Enums\ClaseRetribucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoRetribucion extends Model
{
    protected $table = 'tipo_retribucion';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'seguridad_social',
        'importe',
        'fecha',
        'curso_docente_id',
    ];

    protected $casts = [
        'tipo'             => ClaseRetribucion::class,
        'seguridad_social' => 'float',
        'importe'          => 'float',
        'fecha'            => 'date',
    ];

    public function cursoDocente(): BelongsTo
    {
        return $this->belongsTo(CursoDocente::class, 'curso_docente_id', 'id');
    }
}