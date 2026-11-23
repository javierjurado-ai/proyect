@props(['lista' => []])

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($lista as $peticion)
        @php
            $estado = $peticion->estado->value ?? $peticion->estado;
        @endphp

        <x-interfaz.panel.tarjeta titulo="Petición {{ $peticion->id }}">
            @if ($peticion->imagen)
                <img src="{{ asset('storage/' . $peticion->imagen) }}" alt="Petición {{ $peticion->id }}"
                    class="mb-4 h-48 w-full rounded-lg object-cover">
            @else
                <div class="mb-4 flex h-48 items-center justify-center rounded-lg bg-stone-100 text-sm text-slate-400">
                    Sin imagen
                </div>
            @endif

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">Fecha</dt>
                    <dd class="font-semibold">{{ $peticion->fecha?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Estado</dt>
                    <dd>{{ $estado ? ucfirst(strtolower(str_replace('_', ' ', $estado))) : '—' }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-slate-500">Observaciones</dt>
                    <dd>{{ $peticion->observaciones ?? '—' }}</dd>
                </div>
            </dl>
        </x-interfaz.panel.tarjeta>
    @empty
        <p class="col-span-full text-sm text-slate-500">Todavía no hay peticiones de logística.</p>
    @endforelse
</div>