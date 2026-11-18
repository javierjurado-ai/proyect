<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

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
        'contrasena' => 'hashed',
    ];

    public function getAuthPassword()
    {
        return $this->contrasena;
    }
}