<?php

namespace App\Providers;

use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Todas las vistas reciben $rol, $nombre y $primerNombre del usuario conectado.
        // Si un controlador ya los pasa, se usa lo que mande el controlador.
        View::composer('*', function ($view) {
            static $datos = null;

            if ($datos === null) {
                $datos = ['rol' => null, 'nombre' => '', 'primerNombre' => ''];

                if ($personaId = Auth::user()?->persona_id) {
                    $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

                    $datos = [
                        'rol'          => Rol::where('persona_id', $personaId)->value('rol')?->value,
                        'nombre'       => collect([$persona?->nombre, $persona?->primer_apellido, $persona?->segundo_apellido])->filter()->implode(' '),
                        'primerNombre' => $persona?->nombre ?? '',
                    ];
                }
            }

            foreach ($datos as $clave => $valor) {
                if (! $view->offsetExists($clave)) {
                    $view->with($clave, $valor);
                }
            }
        });
    }
}