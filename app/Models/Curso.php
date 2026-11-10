

namespace App\Models;

use Illuminate\Database\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $table = 'curso';
    protected $primaryKey = 'idgrupo';
    public $timestamps = false;

    protected $fillable = [
        'curso_idcurso',
        'codigo_grupo',
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
    ];

    // Relación recursiva
    public function cursoPadre()
    {
        return $this->belongsTo(Curso::class, 'curso_idcurso', 'idgrupo');
    }
}