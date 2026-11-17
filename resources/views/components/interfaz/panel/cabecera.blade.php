
@props([
    'titulo',
    'subtitulo' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-end justify-between gap-4']) }}>
    <div>
        <h1 class="text-3xl font-bold text-marca-950">{{ $titulo }}</h1>
        @if ($subtitulo)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitulo }}</p>
        @endif
    </div>

    @isset($acciones)
        <div class="flex flex-wrap gap-3">{{ $acciones }}</div>
    @endisset
</div>
