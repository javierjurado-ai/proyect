<?php

namespace App\Models;

use App\Models\Enums\TipoCentro;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Centro extends Model
{
    protected $table = 'centro';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // 'responsable' ya no existe: ahora va en centro_contactos
    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'provincia',
        'municipio',
        'codigo_postal',
        'numero_total_aula',
        'tipo_centro',
        'empresa_id'
    ];

    protected $casts = [
        'tipo_centro'       => TipoCentro::class,
        'numero_total_aula' => 'integer',
    ];

    // Un centro pertenece a una empresa (empresa_id puede ser NULL)
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    // Un centro tiene muchos cursos
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'centro_id', 'id');
    }

       // Un centro tiene muchas aulas directamente por centro_id
    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'centro_id', 'id');
    }

    // Un centro tiene muchos contactos (coordinador / responsable)
    public function contactos(): HasMany
    {
        return $this->hasMany(CentroContactos::class, 'centro_id', 'id');
    }
}