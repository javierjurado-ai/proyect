<?php

namespace App\Models;

use App\Models\Enums\TipoRol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rol extends Model
{
    protected $table = 'rol';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'rol',
        'persona_id',
    ];


    protected $casts = [
        'rol' => TipoRol::class,
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }
}