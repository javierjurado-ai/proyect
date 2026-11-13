<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoEmpresa extends Model
{
    protected $table = 'grupo_empresa';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'curso_id',
        'empresa_id'
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(
            Curso::class,
            'curso_id',
            'id'
        );
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(
            Empresa::class,
            'empresa_id',
            'id'
        );
    }
}