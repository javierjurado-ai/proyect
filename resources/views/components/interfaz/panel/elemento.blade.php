
@props([
    'nombre',
    'detalle' => null,
    'etiqueta' => null,
    'color' => 'gris',
])

<li {{ $attributes->merge(['class' => 'flex items-center justify-between gap-4 py-3']) }}>
    <div class="min-w-0">
        <p class="text-sm font-semibold text-slate-900">{{ $nombre }}</p>
        @if ($detalle)
            <p class="text-xs text-slate-500">{{ $detalle }}</p>
        @endif
    </div>

    @if ($etiqueta !== null)
        <x-panel.etiqueta :color="$color">{{ $etiqueta }}</x-panel.etiqueta>
    @endif
</li>
