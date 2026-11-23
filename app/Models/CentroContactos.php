<?php

namespace App\Models;

use App\Models\Enums\Contacto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CentroContactos extends Model
{
    protected $table = 'centro_contactos';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    protected $fillable = [
        'centro_id',
        'curso_id',
        'nombre',
        'contacto',
        'telefono_personal',
        'email_personal',
    ];

    protected $casts = [
        'contacto' => Contacto::class,
    ];

    public function centro(): BelongsTo
    {
        return $this->belongsTo(
            Centro::class,
            'centro_id',
            'id'
        );
    }
    public function curso(): BelongsTo
    {
        return $this->belongsTo(
            Curso::class,
            'curso_id',
            'id'
        );
    }
}