<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PeticionTicket extends Model
{
    protected $table = 'peticion_ticket';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'peticiones_logistica_id',
        'titulo'
    ];

    protected $casts = [];

    public function mensajes(): HasMany
    {
        return $this->hasMany(PeticionMensajes::class, 'peticion_ticket_id', 'id');
    }
    public function ultimoMensaje(): HasOne
    {
        return $this->hasOne(PeticionMensajes::class, 'peticion_ticket_id', 'id')->latestOfMany();
    }

    public function peticionLogistica(): BelongsTo
    {
        return $this->belongsTo(PeticionesLogistica::class, 'peticiones_logistica_id');
    }
}
