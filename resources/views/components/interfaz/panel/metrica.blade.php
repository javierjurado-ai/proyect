
@props([
    'titulo',
    'valor',
    'detalle' => null,
    'tono' => 'normal',
])

@php
    $colorDetalle = [
        'normal' => 'text-slate-600',
        'alerta' => 'text-red-700',
        'aviso'  => 'text-amber-700',
        'ok'     => 'text-emerald-700',
    ][$tono] ?? 'text-slate-600';
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-stone-200 bg-white p-5']) }}>
    <p class="text-sm text-slate-600">{{ $titulo }}</p>
    <p class="mt-2 text-3xl font-bold text-marca-950">{{ $valor }}</p>
    @if ($detalle)
        <p class="mt-2 text-xs {{ $colorDetalle }}">{{ $detalle }}</p>
    @endif
</div>
