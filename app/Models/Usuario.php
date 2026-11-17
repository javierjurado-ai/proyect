<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
     protected $table = 'usuario';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'usuario',
        'contrasena',
        'persona_id',
    ];

    protected $hidden = ['contrasena'];

    protected $casts = [
        'contrasena' => 'hashed', // cifra sola al asignar
    ];

    // Laravel buscará la contraseña aquí en vez de "password"
    public function getAuthPassword()
    {
        return $this->contrasena;
    }
}

