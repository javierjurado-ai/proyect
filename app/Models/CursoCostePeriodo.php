<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoCostePeriodo extends Model
{
    protected $table = 'curso_coste_periodo';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'mes',
        'importe',
        'coste_id',
    ];

    protected $casts = [
        'mes'     => 'integer',
        'importe' => 'decimal:2',
    ];

    // Cada periodo pertenece a un coste
    public function coste(): BelongsTo
    {
        return $this->belongsTo(Coste::class, 'coste_id', 'id');
    }
}