<?php

namespace App\Models;

use App\Models\Enums\Tipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComunicacionFundae extends Model
{
    protected $table = 'comunicacion_fundae';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    // 'referencia' ya no existe en la BD
    protected $fillable = [
        'curso_id',
        'tipo',
        'fecha_limite',
        'fecha_envio',
    ];

    protected $casts = [
        'tipo'         => Tipo::class,
        'fecha_limite' => 'date',
        'fecha_envio'  => 'date',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }
}