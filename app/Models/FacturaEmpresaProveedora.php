<?php

namespace App\Models;

use App\Models\Enums\EstadoFacturaProveedora;
use App\Models\Enums\FormaPago;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Facturas RECIBIDAS de empresas proveedoras / formadoras o de docentes autónomos
class FacturaEmpresaProveedora extends Model
{
    protected $table = 'factura_empresa_proveedora';
    protected $primaryKey = 'id';
    public $timestamps = false;


    protected $fillable = [
        'empresa_id',
        'docente_id',
        'curso_id',
        'numero_factura',
        'fecha',
        'fecha_registro',
        'fecha_vencimiento',
        'proveedor_cif',
        'proveedor_nombre',
        'base_imponible',
        'total_iva',
        'retencion_irpf',
        'total',
        'forma_pago',
        'estado',
        'fecha_pago',
        'documento',
        'persona_id',
        'observaciones',
    ];

    protected $casts = [
        'estado'            => EstadoFacturaProveedora::class,
        'forma_pago'        => FormaPago::class,
        'fecha'             => 'date',
        'fecha_registro'    => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_pago'        => 'date',
        'base_imponible'    => 'decimal:2',
        'total_iva'         => 'decimal:2',
        'retencion_irpf'    => 'decimal:2',
        'total'             => 'decimal:2',
    ];

    // Empresa con rol PROVEEDOR o FORMADORA
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    // Docente autónomo
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'docente_id', 'id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    // Quién la registra
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(FacturaEmpresaProveedoraLinea::class, 'factura_empresa_proveedora_id', 'id');
    }

    // Entradas de almacén generadas por esta compra
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoAlmacen::class, 'factura_empresa_proveedora_id', 'id');
    }

    // Nombre del emisor, sea empresa o docente: $factura->nombre_emisor
    public function getNombreEmisorAttribute(): ?string
    {
        if ($this->proveedor_nombre) {
            return $this->proveedor_nombre;
        }
        if ($this->empresa) {
            return $this->empresa->nombre;
        }
        if ($this->docente) {
            return collect([$this->docente->nombre, $this->docente->primer_apellido, $this->docente->segundo_apellido])
                ->filter()->implode(' ');
        }

        return null;
    }
}
