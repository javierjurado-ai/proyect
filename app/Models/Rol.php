<?php

namespace App\Models;

use App\Models\Enums\RolEmpresa;
use App\Models\Enums\TipoRol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo de roles (tabla `rol`).
 *  - Roles de persona: columna `rol` (ADMINISTRADOR, COORDINADOR, COMERCIAL, LOGISTA, DOCENTE, ALUMNO).
 *    Se asignan en la tabla `rol_persona` (persona_id, rol_id).
 *  - Roles de empresa: columna `rol_empresa` (FORMADORA, CLIENTE, PROVEEDOR).
 *    Se asignan en la tabla `rol_empresa` (empresa_id, rol_id).
 * Cada fila tiene rellena UNA de las dos columnas; la otra va a NULL.
 */
class Rol extends Model
{
    protected $table = 'rol';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'rol',
        'rol_empresa',
    ];

    protected $casts = [
        'rol'         => TipoRol::class,
        'rol_empresa' => RolEmpresa::class,
    ];

    // Roles que dan acceso a la aplicación, por orden de prioridad.
    // Si una persona tiene varios, entra con el primero de esta lista.
    public const ROLES_DE_ACCESO = ['ADMINISTRADOR', 'COORDINADOR', 'COMERCIAL', 'LOGISTA'];

    public function personas(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'rol_persona', 'rol_id', 'persona_id');
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'rol_empresa', 'rol_id', 'empresa_id');
    }

    /* =========================================================
     *  AYUDAS PARA SABER EL ROL DE QUIEN HA ENTRADO
     * ========================================================= */

    // Todos los roles de una persona, como texto: ['COORDINADOR', 'DOCENTE']
    public static function dePersona(?int $personaId): Collection
    {
        if (! $personaId) {
            return collect();
        }

        return DB::table('rol_persona')
            ->join('rol', 'rol.id', '=', 'rol_persona.rol_id')
            ->where('rol_persona.persona_id', $personaId)
            ->whereNotNull('rol.rol')
            ->pluck('rol.rol')
            ->unique()
            ->values();
    }

    // El rol con el que entra la persona (el de más prioridad de ROLES_DE_ACCESO), o null
    // si no tiene ninguno que dé acceso (por ejemplo, solo DOCENTE o ALUMNO).
    public static function principal(?int $personaId): ?string
    {
        $suyos = self::dePersona($personaId);

        return collect(self::ROLES_DE_ACCESO)->first(fn ($r) => $suyos->contains($r));
    }

    /* =========================================================
     *  AYUDAS PARA ASIGNAR ROLES
     * ========================================================= */

    // id de la fila del catálogo para un rol de persona (la crea si no existe)
    public static function idDePersona(TipoRol|string $rol): int
    {
        $valor = $rol instanceof TipoRol ? $rol->value : strtoupper($rol);

        return self::where('rol', $valor)->value('id')
            ?? self::create(['rol' => $valor, 'rol_empresa' => null])->id;
    }

    // id de la fila del catálogo para un rol de empresa (la crea si no existe)
    public static function idDeEmpresa(RolEmpresa|string $rol): int
    {
        $valor = $rol instanceof RolEmpresa ? $rol->value : strtoupper($rol);

        return self::where('rol_empresa', $valor)->whereNull('rol')->value('id')
            ?? self::create(['rol' => null, 'rol_empresa' => $valor])->id;
    }
}