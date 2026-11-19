@props(['inicial' => null])

@php
    // Cada <x-slot:nombre title="Texto"> declarado aparece en $__laravel_slots
    $paneles = collect($__laravel_slots ?? [])
        ->except('__default')
        ->filter(fn ($slot) => ! $slot->isEmpty());

    $primera = $inicial ?? $paneles->keys()->first();
@endphp

<div x-data="{ tab: '{{ $primera }}' }">

    {{-- Botones --}}
    <div class="mb-4 flex gap-2 border-b border-slate-200">
        @foreach ($paneles as $clave => $slot)
            <button type="button" @click="tab = '{{ $clave }}'"
                :class="tab === '{{ $clave }}'
                    ? 'border-b-2 border-marca-800 text-marca-800'
                    : 'text-slate-500 hover:text-slate-700'"
                class="px-4 py-2 text-sm font-semibold">
                {{ $slot->attributes->get('title', \Illuminate\Support\Str::headline($clave)) }}
            </button>
        @endforeach
    </div>

    {{-- Paneles --}}
    @foreach ($paneles as $clave => $slot)
        <div x-show="tab === '{{ $clave }}'" x-cloak>
            {{ $slot }}
        </div>
    @endforeach

</div>