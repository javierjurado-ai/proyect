<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuario';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // La tabla usuario no tiene columna remember_token.
    // Sin esto, Auth::login($usuario, true) ("recordar") lanza error SQL.
    protected $rememberTokenName = false;

    protected $fillable = [
        'usuario',
        'contrasena',
        'persona_id',
    ];

    protected $hidden = ['contrasena'];

    protected $casts = [
        'contrasena' => 'hashed',
    ];

    // La columna de la contraseña se llama 'contrasena', no 'password'
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    // Laravel 11/12 usa este nombre al re-hashear la contraseña en el login
    public function getAuthPasswordName()
    {
        return 'contrasena';
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }
}