<?php
namespace App\Models;

use App\Models\Enums\Turno;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{

    protected $table = 'curso';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'formacion_id',
        'codigo_curso',
        'empresa_formadora',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_final',
        'turno',
        'aula',
        'aula_virtual',
        'tecnico_comunidad',
        'observaciones',
        'peticiones_logistica_id',
        'centro_id',
        'presupuesto_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'turno' => Turno::class
    ];

    // --- Relaciones ---

    // Relación: Un curso pertenece a una formación principal (curso_idcurso -> idcurso)
    public function formacion()
    {
        return $this->belongsTo(Formacion::class, 'formacion_id', 'idcurso');
    }

    // Relación: Un curso pertenece a un presupuesto (presupuesto_id -> id)
    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'presupuesto_id', 'id');
    }

    // Relación: Un curso pertenece a una petición logística (peticiones_logistica_id -> id)
    public function peticionLogistica()
    {
        return $this->belongsTo(PeticionesLogistica::class, 'peticiones_logistica_id', 'id');
    }

    // Relación: Un curso pertenece a un centro (centro_id -> id)
    public function centro()
    {
        return $this->belongsTo(Centro::class, 'centro_id', 'id');
    }

}