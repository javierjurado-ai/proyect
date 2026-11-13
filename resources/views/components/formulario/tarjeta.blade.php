@props(['titulo' => null, 'subtitulo' => null])

<div {{ $attributes->merge(['class' => 'mx-auto max-w-4xl rounded-xl bg-white p-8 shadow']) }}>
    @if ($titulo)
        <h2 class="mb-1 text-2xl font-bold text-slate-800">{{ $titulo }}</h2>
    @endif
    @if ($subtitulo)
        <p class="mb-6 text-sm text-slate-500">{{ $subtitulo }}</p>
    @endif

    {{ $slot }}
</div>