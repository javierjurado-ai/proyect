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
        'grupo_idgrupo',
        'empresa_id'
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(
            Curso::class,
            'grupo_idgrupo',
            'idgrupo'
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