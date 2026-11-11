<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoEmpresa extends Model
{
    protected $table = 'grupo_empresa';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'grupo_idgrupo',
        'empresa_id'
    ];

    // Relación con Curso
    public function curso(): BelongsTo
    {
        return $this->belongsTo(
            Curso::class,
            'grupo_idgrupo',
            'idgrupo'
        );
    }

    // Relación con Empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(
            Empresa::class,
            'empresa_id',
            'id'
        );
    }
}