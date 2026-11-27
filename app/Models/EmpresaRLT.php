<?php

namespace App\Models;

use App\Models\Enums\LOPD;
use App\Models\Enums\Valor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaRLT extends Model
{
    protected $table = 'empresa_rlt';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'informe_rlt',
        'valor_informe',
        'fecha_dis',
        'resuelto_15',
        'empresa_id',
        'curso_id'
    ];
    protected $casts = [
        'informe_rlt' => LOPD::class,
        'resuelto_15' => LOPD::class,
        'valor_informe' => Valor::class
    ];
   
   public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }
}
