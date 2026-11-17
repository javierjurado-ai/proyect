
@props([
    'usuario' => '',
    'rol' => '',
])

@php
    $nombre = $usuario ?: 'Usuario';
    $nombreRol = $rol ? ucfirst(strtolower($rol)) : '';
@endphp

<header class="sticky top-0 z-10 flex items-center gap-4 border-b border-stone-200 bg-white px-4 py-3 sm:px-6 lg:px-8">
    <button type="button" id="abrir-menu" aria-label="Abrir menú" aria-controls="menu" aria-expanded="false"
        class="flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 hover:bg-stone-100 lg:hidden">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" aria-hidden="true">
            <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <form action="{{ url('/buscar') }}" method="GET" role="search" class="w-full max-w-md">
        <label for="buscar" class="sr-only">Buscar</label>
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.5-3.5" />
            </svg>
            <input id="buscar" name="q" type="search" value="{{ request('q') }}"
                placeholder="Buscar curso, alumno o empresa"
                class="w-full rounded-lg bg-stone-100 py-2 pl-10 pr-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-marca-700">
        </div>
    </form>

    <div class="ml-auto flex shrink-0 items-center gap-3">
        <div class="hidden text-right sm:block">
            <p class="text-sm font-semibold text-slate-900">{{ $nombre }}</p>
            @if ($nombreRol)
                <p class="text-xs text-slate-500">{{ $nombreRol }}</p>
            @endif
        </div>

        <x-interfaz.panel.avatar :nombre="$nombre" />

        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <x-formulario.boton type="submit" variante="secundario" class="px-4 text-sm">
                Cerrar sesión
            </x-formulario.boton>
        </form>
    </div>
</header>
