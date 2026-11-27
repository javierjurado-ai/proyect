@props([
    'href',
    'nombre',
    'ultimoMensaje' => null,
    'hora' => null,
    'noLeidos' => 0,
    'activa' => false,
])

{{-- Elemento de la lista de conversaciones --}}
<a href="{{ $href }}" @class([
    'flex items-center gap-3 border-b border-slate-100 px-4 py-3 transition',
    'bg-teal-800/5' => $activa,
    'hover:bg-slate-50' => ! $activa,
])>
    <x-ticket.avatar :nombre="$nombre" />

    <div class="min-w-0 flex-1">
        <div class="flex items-center justify-between gap-2">
            <p class="truncate text-sm font-medium text-slate-800">{{ $nombre }}</p>
            @if ($hora)
                <span @class([
                    'shrink-0 text-xs',
                    'font-semibold text-teal-800' => $noLeidos > 0,
                    'text-slate-400' => $noLeidos == 0,
                ])>{{ $hora }}</span>
            @endif
        </div>

        <div class="mt-0.5 flex items-center justify-between gap-2">
            <p class="truncate text-sm text-slate-500">{{ $ultimoMensaje ?? 'Sin mensajes todavía' }}</p>
            @if ($noLeidos > 0)
                <span class="inline-flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-teal-800 px-1.5 text-xs font-semibold text-white">
                    {{ $noLeidos > 99 ? '99+' : $noLeidos }}
                </span>
            @endif
        </div>
    </div>
</a>
