@props(['nombre' => '?', 'tamano' => 'md'])

@php
    $iniciales = collect(explode(' ', trim($nombre)))
        ->filter()
        ->take(2)
        ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');

    $clasesTamano = match ($tamano) {
        'sm' => 'h-8 w-8 text-xs',
        'lg' => 'h-12 w-12 text-base',
        default => 'h-10 w-10 text-sm',
    };
@endphp

<div {{ $attributes->merge(['class' => "$clasesTamano flex shrink-0 items-center justify-center rounded-full bg-teal-800/10 font-semibold text-teal-800"]) }}>
    {{ $iniciales ?: '?' }}
</div>
