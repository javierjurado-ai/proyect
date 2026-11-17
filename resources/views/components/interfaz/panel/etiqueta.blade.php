@props(['color' => 'gris'])

@php
    $colores = [
        'gris'    => 'bg-stone-100 text-stone-700',
        'rojo'    => 'bg-red-50 text-red-700',
        'ambar'   => 'bg-amber-50 text-amber-800',
        'verde'   => 'bg-emerald-50 text-emerald-700',
        'azul'    => 'bg-sky-50 text-sky-800',
        'violeta' => 'bg-violet-50 text-violet-700',
    ];
    $clases = $colores[$color] ?? $colores['gris'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold $clases"]) }}>
    {{ $slot }}
</span>
