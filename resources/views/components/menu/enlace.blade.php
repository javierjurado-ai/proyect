
Menu enlace.blade · PHP
@props([
    'ruta',
    'icono' => 'punto',
    'roles' => null,
    'rol' => '',
    'contador' => null,
])
 
@php
    $visible = $roles === null || in_array(strtoupper($rol), explode(',', strtoupper($roles)), true);
    $existe = \Illuminate\Support\Facades\Route::has($ruta);
    $activo = $existe && request()->routeIs($ruta);
 
    // Trazos SVG de cada icono
    $iconos = [
        'inicio'   => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/>',
        'libro'    => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2V5Z"/><path d="M4 19a2 2 0 0 1 2-2h13"/>',
        'edificio' => '<path d="M4 21V4h10v17"/><path d="M14 9h6v12"/><path d="M8 8h2M8 12h2M8 16h2"/>',
        'personas' => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 2.7-5 6-5s6 2 6 5"/><path d="M16 5a3 3 0 0 1 0 6M18 15c2 .6 3 2.3 3 5"/>',
        'caja'     => '<path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
        'mensaje'  => '<path d="M4 5h16v11H9l-5 4V5Z"/>',
        'dinero'   => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
        'ajustes'  => '<circle cx="12" cy="12" r="3"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/>',
        'punto'    => '<circle cx="12" cy="12" r="3"/>',
    ];
    $trazo = $iconos[$icono] ?? $iconos['punto'];
@endphp
 
@if ($visible)
    <a href="{{ $existe ? route($ruta) : '#' }}" @if ($activo) aria-current="page" @endif
        {{ $attributes->class([
            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
            'bg-white/10 text-white' => $activo,
            'text-marca-100 hover:bg-white/5 hover:text-white' => ! $activo,
        ]) }}>
        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $trazo !!}</svg>
 
        <span class="flex-1">{{ $slot }}</span>
 
        @if ($contador)
            <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1.5 text-xs font-bold text-marca-950">
                {{ $contador }}
            </span>
        @endif
    </a>
@endif