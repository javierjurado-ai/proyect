<?php

namespace App\Models;

use App\Models\Enums\RolEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaRol extends Model
{
    protected $table = 'empresa_rol';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Una empresa puede tener varios roles 
    protected $fillable = [
        'empresa_id',
        'rol',
    ];

    protected $casts = [
        'rol' => RolEmpresa::class,
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }
}
