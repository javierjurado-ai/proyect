@props([
    'type' => 'button',
    'variante' => 'primario',
])

@php
    $estilos = [
        'primario'   => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-300',
        'secundario' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-200',
    ][$variante] ?? '';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => "inline-flex items-center justify-center gap-2 rounded-lg px-5 py-2 font-medium transition duration-200 focus:outline-none focus:ring-4 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 $estilos",
    ]) }}
>
    {{ $slot }}
</button>