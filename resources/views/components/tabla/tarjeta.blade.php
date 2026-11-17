@props(['titulo', 'subtitulo' => null])

<div {{ $attributes->merge(['class' => 'mx-auto max-w-6xl rounded-xl bg-white p-8 shadow']) }}>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ $titulo }}</h2>
            @if ($subtitulo)
                <p class="mt-1 text-sm text-slate-500">{{ $subtitulo }}</p>
            @endif
        </div>

        @isset($acciones)
            <div class="flex gap-3">{{ $acciones }}</div>
        @endisset
    </div>

    @if (session('ok'))
        <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    {{ $slot }}
</div>