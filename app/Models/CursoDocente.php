<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoDocente extends Model
{
    protected $table = 'curso_docente';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    // OJO: en la BD hay dos columnas parecidas:
    //  - 'catagoria' (con errata): enum('CUENTA AJENA','AUTONOMO')
    //  - 'categoria': varchar, por defecto 'DOCENTE'
    protected $fillable = [
        'curso_id',
        'persona_id',
        'empresa_id',
        'numero_horas',
        'catagoria',
        'tipo_contrato',
        'convenio',
        'categoria',
        'IBAN',
        'fecha_inicio',
        'fecha_fin',
        'horas_presenciales',
        'horas_virtuales',
    ];

    // No devolver el IBAN en las respuestas JSON
    protected $hidden = ['IBAN'];

    protected $casts = [
        'numero_horas'       => 'decimal:2',
        'fecha_inicio'       => 'date',
        'fecha_fin'          => 'date',
        'horas_presenciales' => 'float',
        'horas_virtuales'    => 'float',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }
}