@props([
    'titulo',
    'roles' => null,
    'rol' => '',
])

@php
    $visible = $roles === null || in_array(strtoupper($rol), explode(',', strtoupper($roles)), true);
@endphp

@if ($visible)
    <div {{ $attributes->merge(['class' => 'mt-6 flex flex-col gap-1']) }}>
        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-marca-100/60">{{ $titulo }}</p>
        {{ $slot }}
    </div>
@endif
