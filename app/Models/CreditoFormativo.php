<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditoFormativo extends Model
{
    protected $table = 'credito_formativo';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'año',
        'importe_total',
        'importe_consumido',
    ];

        public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }
}