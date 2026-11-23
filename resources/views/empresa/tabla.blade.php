<x-tabla.tabla vacio="Todavía no hay empresas guardadas."
    :columnas="['CIF', 'Nombre', ['Total trabajadores', 'right'], ['Acciones', 'center']]">
    @foreach ($empresas as $e)
        <x-tabla.fila>
            <td class="px-4 py-3 font-mono text-slate-700">{{ $e->cif }}</td>
            <td class="px-4 py-3 font-medium text-slate-800">
                <a href="{{ route('empresa.visualizar', $e) }}" class="font-semibold text-marca-900 hover:underline">{{ $e->nombre }}</a>
            </td>
            <x-tabla.celda tipo="numero" :valor="$e->total_trabajadores" />

            {{-- ACCIONES --}}
            <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="inline-flex items-center gap-2">
                    <x-tabla.boton-editar :href="route('empresa.editar', $e->id)" />
                    <x-tabla.boton-eliminar :action="route('empresa.eliminar', $e->id)" />
                </div>
            </td>
        </x-tabla.fila>
    @endforeach
</x-tabla.tabla>