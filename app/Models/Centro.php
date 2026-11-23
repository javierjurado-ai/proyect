<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\TipoCentro;

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
        'provincia',
        'municipio',
        'codigo_postal',
        'numero_total_aula',
        'tipo_centro',
    ];

    protected $casts =[
        'tipo_centro' => TipoCentro::class
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

    // Relación: Un centro tiene muchos contactos 
    public function contactos(): HasMany
    {
        return $this->hasMany(
            CentroContactos::class,
            'centro_id',
            'id'
        );
    }

}