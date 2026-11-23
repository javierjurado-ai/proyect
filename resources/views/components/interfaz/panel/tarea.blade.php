
@props([
    'etiqueta',
    'color' => 'gris',
    'titulo',
    'detalle' => null,
    'accion' => null,
    'href' => '#',
])

<li {{ $attributes->merge(['class' => 'flex items-center gap-4 rounded-lg bg-stone-100 px-4 py-3']) }}>
    <x-interfaz.panel.etiqueta :color="$color" class="shrink-0">{{ $etiqueta }}</x-interfaz.panel.etiqueta>

    <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-slate-900">{{ $titulo }}</p>
        @if ($detalle)
            <p class="text-xs text-slate-500">{{ $detalle }}</p>
        @endif
    </div>

    @if ($accion)
        <a href="{{ $href }}"
            class="shrink-0 text-sm font-semibold text-marca-900 underline underline-offset-2 hover:text-marca-700">
            {{ $accion }}
        </a>
    @endif
</li>
