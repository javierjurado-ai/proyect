<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\Tipo;
class ComunicacionFundae extends Model
{
    protected $table = 'comunicacion_fundae';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    protected $fillable = [
        'grupo_idgrupo', 
        'tipo', 
        'fecha_limite',
        'fecha_envio',
        'referencia'
    ];

    protected $casts = [
        'tipo' => Tipo::class,
        'fecha_limite' => 'date',
        'fecha_envio' => 'date'
    ];
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(
            Curso::class,
            'grupo_idgrupo',
            'idgrupo'
        );
    }
}
