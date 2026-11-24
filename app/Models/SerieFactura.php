<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SerieFactura extends Model
{
    protected $table = 'serie_factura';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // 'F' ordinarias, 'R' rectificativas… una fila por serie y año
    protected $fillable = [
        'codigo',
        'descripcion',
        'anio',
        'ultimo_numero',
    ];

    protected $casts = [
        'anio'          => 'integer',
        'ultimo_numero' => 'integer',
    ];

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'serie_factura_id', 'id');
    }
}
