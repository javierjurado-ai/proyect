<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeticionMensajes extends Model
{
    protected $table = 'peticion_mensajes';
    protected $primaryKey = 'id';
    public $timestamps = false;
protected $fillable = ['mensaje', 'fecha_creada', 'hora_creada', 'persona_id'];

    protected $casts = [
        'fecha_creada' => 'date',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
