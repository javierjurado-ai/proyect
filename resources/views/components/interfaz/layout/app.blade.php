
@props([
    'titulo' => 'Inicio',
    'rol' => null,
    'usuario' => '',
    'contadores' => [],
])

@php
    // Acepta el enum TipoRol o un texto
    $rol = $rol instanceof \BackedEnum ? $rol->value : strtoupper((string) $rol);
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    {{-- Colores de la marca: bg-marca-950, text-marca-900, etc. --}}
    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            --color-marca-50: #EEF6F6;
            --color-marca-100: #CFE3E4;
            --color-marca-700: #1D5C63;
            --color-marca-800: #144950;
            --color-marca-900: #0E3B41;
            --color-marca-950: #082A2F;
        }
    </style>
</head>

<body class="bg-stone-100 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <x-interfaz.layout.menu :rol="$rol" :contadores="$contadores" />

        {{-- Fondo oscuro detrás del menú en móvil --}}
        <div id="fondo-menu" class="fixed inset-0 z-20 hidden bg-black/40 lg:hidden"></div>

        <div class="flex min-w-0 flex-1 flex-col">
            <x-interfaz.layout.barra-superior :usuario="$usuario" :rol="$rol" />

            <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Abrir y cerrar el menú en móvil --}}
    <script>
        const menu = document.getElementById('menu');
        const fondo = document.getElementById('fondo-menu');
        const botonMenu = document.getElementById('abrir-menu');

        function alternarMenu() {
            const abierto = menu.classList.toggle('-translate-x-full') === false;
            fondo.classList.toggle('hidden', !abierto);
            botonMenu.setAttribute('aria-expanded', abierto);
        }

        botonMenu.addEventListener('click', alternarMenu);
        fondo.addEventListener('click', alternarMenu);
    </script>
    @stack('scripts')
</body>

</html>
