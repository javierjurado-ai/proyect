<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    
    protected $fillable = [
        'cif', 
        'nombre', 
        'total_trabajadores',
    ];

    protected $casts = [
    ];

    /**
     * Una empresa tiene muchos centros de trabajo.
     */
    public function centros()
    {
        return $this->hasMany(Centro::class, 'empresa_id', 'id');
    }

    /**
     * Una empresa tiene muchas personas asociadas.
     */
    public function personas()
    {
        return $this->hasMany(Persona::class, 'empresa_id', 'id');
    }

    /**
     * Una empresa tiene muchos créditos formativos.
     */
    public function creditosFormativos()
    {
        return $this->hasMany(CreditoFormativo::class, 'empresa_id', 'id');
    }

    /**
     * Relación muchos a muchos con cursos a través de grupo_empresa.
     */
    public function cursosGrupo()
    {
        return $this->belongsToMany(Curso::class, 'grupo_empresa', 'empresa_id', 'curso_id');
    }
}
