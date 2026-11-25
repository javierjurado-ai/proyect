<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'token.temporal' => \App\Http\Middleware\TokenTemporal::class,
            'rol'            => \App\Http\Middleware\ComprobarRol::class,
        ]);

        // Rutas JSON (Postman) que no piden token CSRF.
        // OJO: Laravel decide solo por la dirección, no por el método. Por eso NO están
        // 'persona', 'empresa', 'formacion', 'centro', '   presupuesto' ni 'curso/{id}':
        // comparten dirección con los formularios y botones de borrar de la web, que sí
        // deben llevar CSRF.
        $middleware->validateCsrfTokens(except: [
            'credito', 'credito/*',
            'comunicacionFundae', 'comunicacionFundae/*',
            'peticionesLogistica', 'peticionesLogistica/*',
            'centro-contactos/*', 'api/centro-contactos',
            'curso/rolDocente/*', 'curso/CursoDocente/*',
            'curso/rolAlumno/*', 'curso/CursoAlumno/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Errores en JSON para las rutas de la API (y para el login, que va por fetch)
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is(
                'persona/iniciarSesion',
                'credito', 'credito/*',
                'comunicacionFundae', 'comunicacionFundae/*',
                'peticionesLogistica', 'peticionesLogistica/*',
                'centro-contactos/*', 'api/centro-contactos',
                'curso/listar', 'curso/rolDocente/*', 'curso/CursoDocente/*',
                'curso/rolAlumno/*', 'curso/CursoAlumno/*'
            ) || $request->expectsJson();
        });
    })->create();