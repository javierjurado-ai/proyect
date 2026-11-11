<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Centro extends Model
{
    protected $table = 'centro';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'nombre',
        'responsable',
        'direccion',
        'telefono',
        'email',
    ];

    // Relación: Un centro pertenece a una empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    // Relación: Un centro tiene muchos cursos
    public function cursos()
    {
        return $this->hasMany(Curso::class, 'centro_id', 'id');
    }
}