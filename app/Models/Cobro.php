<?php

namespace App\Models;

use App\Models\Enums\FormaPago;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cobro extends Model
{
    protected $table = 'cobro';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Admite cobros parciales: una fectura puede tener varios 

protected $fillable = [
        'factura_id',
        'fecha',
        'importe',
        'forma_pago',
        'referencia',
    ];

    protected $casts = [
        'fecha'      => 'date',
        'importe'    => 'decimal:2',
        'forma_pago' => FormaPago::class,
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id', 'id');
    }
}