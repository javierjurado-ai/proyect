@props(['nombre', 'subtitulo' => null, 'volver' => null])

{{-- Cabecera de la conversación abierta --}}
<header class="flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3">
    @if ($volver)
        {{-- Botón volver, solo visible en móvil --}}
        <a href="{{ $volver }}" class="-ml-1 rounded-md p-1 text-slate-500 transition hover:bg-slate-100 md:hidden"
            aria-label="Volver">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
        </a>
    @endif

    <x-ticket.avatar :nombre="$nombre" />

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-slate-800">{{ $nombre }}</p>
        @if ($subtitulo)
            <p class="truncate text-xs text-slate-500">{{ $subtitulo }}</p>
        @endif
    </div>

    {{-- Acciones extra (por ejemplo, ver pedido o cerrar ticket) --}}
    {{ $slot }}
</header>
