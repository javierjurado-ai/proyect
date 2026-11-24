@props([
    'imagen' => null,                 // texto guardado en la BD (data URI o base64 sin prefijo)
    'alt' => 'Imagen',
    'titulo' => null,
    'subtitulo' => null,
    'vacio' => 'No hay imagen guardada.',
])

@php
    $imagen = trim((string) $imagen);

    // Si se guardó solo el base64 (sin "data:image/...;base64,"), se le añade el prefijo.
    $src = $imagen === ''
        ? null
        : (str_starts_with($imagen, 'data:image/') ? $imagen : 'data:image/png;base64,' . $imagen);
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-stone-200 bg-white p-6 shadow-sm']) }}>
    @if ($titulo || isset($acciones))
        <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                @if ($titulo)
                    <h2 class="text-lg font-bold text-slate-800">{{ $titulo }}</h2>
                @endif
                @if ($subtitulo)
                    <p class="mt-1 text-sm text-slate-500">{{ $subtitulo }}</p>
                @endif
            </div>

            @isset($acciones)
                <div class="flex gap-3">{{ $acciones }}</div>
            @endisset
        </div>
    @endif

    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy"
            class="mx-auto max-h-96 w-auto max-w-full rounded-lg border border-stone-200 object-contain">
    @else
        <div class="rounded-lg border border-dashed border-stone-300 bg-stone-50 px-4 py-10 text-center text-sm text-slate-500">
            {{ $vacio }}
        </div>
    @endif

    {{ $slot }}
</div>