
@props([
    'ruta',
    'roles' => null,
    'rol' => '',
    'contador' => null,
])

@php
    $visible = $roles === null || in_array(strtoupper($rol), explode(',', strtoupper($roles)), true);
    $existe = \Illuminate\Support\Facades\Route::has($ruta);
    $activo = $existe && request()->routeIs($ruta);
@endphp

@if ($visible)
    <a href="{{ $existe ? route($ruta) : '#' }}" @if ($activo) aria-current="page" @endif
        {{ $attributes->class([
            'flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
            'bg-white/10 text-white' => $activo,
            'text-marca-100 hover:bg-white/5 hover:text-white' => ! $activo,
        ]) }}>
        <span>{{ $slot }}</span>

        @if ($contador)
            <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1.5 text-xs font-bold text-marca-950">
                {{ $contador }}
            </span>
        @endif
    </a>
@endif
