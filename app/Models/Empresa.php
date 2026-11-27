<?php

namespace App\Models;

use App\Models\Enums\RolEmpresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $table = 'empresa';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    protected $fillable = [
        'cif',
        'nombre',
        'direccion',
        'codigo_postal',
        'municipio',
        'provincia',
        'email_facturacion',
        'dias_vencimiento',
        'telefono',
        'total_trabajadores',
        'activo',
    ];

    protected $casts = [
        'total_trabajadores' => 'integer',
        'dias_vencimiento'   => 'integer',
        'activo'             => 'boolean',
    ];

    // Una empresa tiene muchos centros
    public function centros(): HasMany
    {
        return $this->hasMany(Centro::class, 'empresa_id', 'id');
    }

    // Una empresa tiene muchos créditos formativos
    public function creditosFormativos(): HasMany
    {
        return $this->hasMany(CreditoFormativo::class, 'empresa_id', 'id');
    }

    // persona ya no tiene empresa_id: la empresa va en curso_alumno / curso_docente
    public function cursoAlumnos(): HasMany
    {
        return $this->hasMany(CursoAlumno::class, 'empresa_id', 'id');
    }

    public function cursoDocentes(): HasMany
    {
        return $this->hasMany(CursoDocente::class, 'empresa_id', 'id');
    }

    // Personas que han sido alumnos de esta empresa
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'curso_alumno', 'empresa_id', 'persona_id')->distinct();
    }

    // Cursos del grupo (tabla grupo_empresa)
    public function cursosGrupo(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'grupo_empresa', 'empresa_id', 'curso_id');
    }

    // Coste de formación de la empresa en cada curso (tabla coste)
    public function costes(): HasMany
    {
        return $this->hasMany(Coste::class, 'empresa_id', 'id');
    }

    // Informes de la representación legal de los trabajadores (tabla empresa_rlt)
    public function informesRlt(): HasMany
    {
        return $this->hasMany(EmpresaRLT::class, 'empresa_id', 'id');
    }

    // --- Roles: FORMADORA, CLIENTE, PROVEEDOR (tabla intermedia rol_empresa → rol.rol_empresa) ---

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_empresa', 'empresa_id', 'rol_id');
    }

    // $empresa->tieneRol(RolEmpresa::Proveedor)
    public function tieneRol(RolEmpresa $rol): bool
    {
        return $this->roles()->where('rol.rol_empresa', $rol->value)->exists();
    }

    // Empresa::conRol(RolEmpresa::Cliente)->get()
    public function scopeConRol(Builder $query, RolEmpresa $rol): Builder
    {
        return $query->whereHas('roles', fn (Builder $q) => $q->where('rol.rol_empresa', $rol->value));
    }

    // --- Qué empresas ve cada rol de persona ---
    //   ADMINISTRADOR: todas (también las que no tienen tipo)
    //   COORDINADOR:   formadoras y clientes
    //   COMERCIAL:     clientes
    //   LOGISTA:       proveedores
    public const TIPOS_POR_ROL = [
        'COORDINADOR' => ['FORMADORA', 'CLIENTE'],
        'COMERCIAL'   => ['CLIENTE'],
        'LOGISTA'     => ['PROVEEDOR'],
    ];

    // Tipos de empresa (RolEmpresa) que puede ver y asignar quien tiene ese rol
    public static function tiposVisiblesPara(?string $rol): array
    {
        if ($rol === 'ADMINISTRADOR') {
            return array_map(fn (RolEmpresa $r) => $r->value, RolEmpresa::cases());
        }

        return self::TIPOS_POR_ROL[$rol] ?? [];
    }

    // Empresa::visiblesPara($rol)->orderBy('nombre')->get()
    public function scopeVisiblesPara(Builder $query, ?string $rol): Builder
    {
        if ($rol === 'ADMINISTRADOR') {
            return $query;
        }

        $tipos = self::tiposVisiblesPara($rol);

        return $tipos
            ? $query->whereHas('roles', fn (Builder $q) => $q->whereIn('rol.rol_empresa', $tipos))
            : $query->whereRaw('1 = 0'); // sin rol conocido no ve ninguna
    }

    // ¿La puede ver quien tiene ese rol?
    public function esVisiblePara(?string $rol): bool
    {
        return static::whereKey($this->getKey())->visiblesPara($rol)->exists();
    }

    // --- Facturación ---

    // Facturas que le emitimos (como CLIENTE)
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'empresa_id', 'id');
    }

    // Facturas que nos emite (como PROVEEDOR / FORMADORA)
    public function facturasProveedora(): HasMany
    {
        return $this->hasMany(FacturaEmpresaProveedora::class, 'empresa_id', 'id');
    }

    // --- Almacén ---

    // Artículos de los que es proveedor habitual
    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class, 'proveedor_habitual_id', 'id');
    }

    // Entradas de almacén por compras a esta empresa
    public function movimientosAlmacen(): HasMany
    {
        return $this->hasMany(MovimientoAlmacen::class, 'proveedor_id', 'id');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'empresa_id', 'id');
    }
}