@props(['nombre' => ''])

@php
    $iniciales = collect(explode(' ', trim($nombre)))
        ->filter()
        ->take(2)
        ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
@endphp

<span aria-hidden="true"
    {{ $attributes->merge(['class' => 'flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-400 text-sm font-bold text-marca-950']) }}>
    {{ $iniciales }}
</span>
