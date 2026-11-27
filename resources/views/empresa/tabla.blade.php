@php
    $colorTipo = ['FORMADORA' => 'violeta', 'CLIENTE' => 'azul', 'PROVEEDOR' => 'ambar'];
@endphp

<x-tabla.tabla vacio="Todavía no hay empresas guardadas."
    :columnas="[['Acciones', 'center'], 'CIF', 'Nombre', 'Tipo', ['Total trabajadores', 'right']]">
    @foreach ($empresas as $e)
        <x-tabla.fila>
            {{-- ACCIONES --}}
            <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="inline-flex items-center gap-2">
                    <x-tabla.boton-editar :href="route('empresa.editar', $e->id)" />
                    <x-tabla.boton-eliminar :action="route('empresa.eliminar', $e->id)" />
                </div>
            </td>

            <td class="px-4 py-3 font-mono text-slate-700">{{ $e->cif }}</td>
            <td class="px-4 py-3 font-medium text-slate-800">
                <a href="{{ route('empresa.visualizar', $e) }}" class="font-semibold text-marca-900 hover:underline">{{ $e->nombre }}</a>
            </td>
            <td class="px-4 py-3">
                <div class="flex flex-wrap gap-1">
                    @forelse ($e->roles->pluck('rol_empresa')->filter() as $t)
                        <x-interfaz.panel.etiqueta :color="$colorTipo[$t->value] ?? 'gris'">{{ ucfirst(strtolower($t->value)) }}</x-interfaz.panel.etiqueta>
                    @empty
                        <span class="text-slate-400">Sin tipo</span>
                    @endforelse
                </div>
            </td>
            <x-tabla.celda tipo="numero" :valor="$e->total_trabajadores" />
        </x-tabla.fila>
    @endforeach
</x-tabla.tabla>
