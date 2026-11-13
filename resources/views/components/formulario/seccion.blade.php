@props(['titulo', 'columnas' => 3])

@php
    // Una columna en pantallas pequeñas, varias a partir de md (768px)
    $grid = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-3',
    ][$columnas] ?? 'grid-cols-1 md:grid-cols-3';
@endphp

<h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-blue-600">{{ $titulo }}</h3>
<div {{ $attributes->merge(['class' => "mb-8 grid gap-5 $grid"]) }}>
    {{ $slot }}
</div>