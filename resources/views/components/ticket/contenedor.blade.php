@props(['activa' => false])

{{-- Layout de dos columnas: lista de conversaciones + conversación abierta --}}
<div {{ $attributes->merge(['class' => 'flex h-[calc(100vh-8rem)] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm']) }}>
    {{-- Columna izquierda: en móvil se oculta cuando hay una conversación abierta --}}
    <aside @class([
        'w-full flex-col border-r border-slate-200 md:flex md:w-80 lg:w-96',
        'hidden' => $activa,
        'flex' => ! $activa,
    ])>
        {{ $lista }}
    </aside>

    {{-- Columna derecha: en móvil solo se muestra si hay conversación abierta --}}
    <section @class([
        'min-w-0 flex-1 flex-col',
        'flex' => $activa,
        'hidden md:flex' => ! $activa,
    ])>
        {{ $slot }}
    </section>
</div>
