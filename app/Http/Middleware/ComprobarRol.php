<?php

namespace App\Http\Middleware;

use App\Models\Rol;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja pasar solo a los roles indicados en la ruta.
 * Uso: Route::middleware('rol:ADMINISTRADOR,LOGISTA')->group(...)
 *
 * Una persona puede tener varios roles (tabla rol_persona): basta con que uno coincida.
 */
class ComprobarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $personaId = $request->user()?->persona_id;

        $tieneRol = $personaId && Rol::dePersona($personaId)
            ->intersect(array_map('strtoupper', $roles))
            ->isNotEmpty();

        if (! $tieneRol) {
            if ($request->expectsJson()) {
                return response()->json(['mensaje' => 'No tienes permiso para acceder a esta sección.'], 403);
            }

            return redirect()
                ->route('inicio.index')
                ->withErrors(['permiso' => 'No tienes permiso para acceder a esa sección.']);
        }

        return $next($request);
    }
}
