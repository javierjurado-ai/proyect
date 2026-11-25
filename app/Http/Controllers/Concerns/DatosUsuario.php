<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Almacen;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Support\Facades\Auth;

// Datos que necesitan las vistas de logística: rol y nombre para el layout,
// y los almacenes de los que es responsable quien ha entrado.
trait DatosUsuario
{
    protected function rolYNombre(): array
    {
        $personaId = Auth::user()->persona_id;
        $rol = Rol::where('persona_id', $personaId)->value('rol');
        $persona = Persona::find($personaId);

        return [
            $rol instanceof \BackedEnum ? $rol->value : $rol,
            collect([$persona?->nombre, $persona?->primer_apellido, $persona?->segundo_apellido])->filter()->implode(' '),
        ];
    }

    // Almacenes de los que es responsable (almacen.responsable_id). Si no es responsable
    // de ninguno, se usan todos los activos.
    protected function misAlmacenes()
    {
        $propios = Almacen::where('activo', true)->where('responsable_id', Auth::user()->persona_id)->orderBy('nombre')->get();

        return $propios->isNotEmpty() ? $propios : Almacen::where('activo', true)->orderBy('nombre')->get();
    }
}
