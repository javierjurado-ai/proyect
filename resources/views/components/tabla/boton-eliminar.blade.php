@props(['action', 'mensaje' => '¿Estás seguro de que deseas eliminar este registro?'])

<form action="{{ $action }}" method="POST" class="inline" onsubmit="return confirm('{{ $mensaje }}');">
    @csrf
    @method('DELETE')
    <button type="submit"
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300']) }}>
        {{ $slot->isEmpty() ? 'Eliminar' : $slot }}
    </button>
</form>