@props(['titulo' => null, 'subtitulo' => null])

<div {{ $attributes->merge(['class' => 'mx-auto max-w-6xl rounded-xl bg-white p-8 shadow']) }}>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            @if ($titulo)
                <h2 class="text-2xl font-bold text-slate-800">{{ $titulo }}</h2>
            @endif
            @if ($subtitulo)
                <p class="mt-1 text-sm text-slate-500">{{ $subtitulo }}</p>
            @endif
        </div>

        @isset($acciones)
            <div class="flex gap-3">{{ $acciones }}</div>
        @endisset
    </div>

    {{ $slot }}
</div>