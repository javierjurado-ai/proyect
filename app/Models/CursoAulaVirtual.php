<?php

namespace App\Models;

use App\Models\enums\LOPD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoAulaVirtual extends Model
{
    protected $table = 'curso_aula_virtual';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'curso_id',
        'medio',
        'conexion',
        'contacto',
        'telefono',
        'bimodal',
        'sin_participantes_en_centro',
        'sin_docentes_en_centro'
    ];
    protected $casts = [
        'bimodal'   => LOPD::class,
        'sin_participantes_en_centro' => LOPD::class,
        'sin_docentes_en_centro' => LOPD::class,
    ];
   
   public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }
  
}
