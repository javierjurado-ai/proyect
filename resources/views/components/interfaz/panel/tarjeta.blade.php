@props([
    'titulo',
    'enlace' => null,
    'textoEnlace' => 'Ver todos',
])

<section {{ $attributes->merge(['class' => 'rounded-xl border border-stone-200 bg-white p-5']) }}>
    <div class="mb-4 flex items-center justify-between gap-4">
        <h2 class="text-lg font-bold text-marca-950">{{ $titulo }}</h2>
        @if ($enlace)
            <a href="{{ $enlace }}"
                class="text-sm font-semibold text-marca-900 underline underline-offset-2 hover:text-marca-700">
                {{ $textoEnlace }}
            </a>
        @endif
    </div>

    {{ $slot }}
</section>
