<x-tabla.tabla vacio="Todavía no hay centros guardados." :columnas="['Nombre', 'Tipo', 'Empresa', 'Municipio', 'Provincia', 'Teléfono', 'Email', ['Aulas', 'right'], ['Acciones', 'center']]">
    @foreach ($centros as $centro)
        <x-tabla.fila class="cursor-pointer hover:bg-stone-50 transition-colors" onclick="window.location='{{ route('centro.visualizar', $centro->id) }}'">
            <x-tabla.celda tipo="destacado">
                <span class="font-semibold text-marca-900">
                    {{ $centro->nombre }}
                </span>
                <span class="block text-xs font-normal text-slate-500">{{ $centro->direccion }} ·
                    {{ $centro->codigo_postal }}</span>
            </x-tabla.celda>
            <x-tabla.celda tipo="etiqueta" color="blue" :valor="$centro->tipo_centro" />
            <x-tabla.celda :valor="$centro->empresa?->nombre" />
            <x-tabla.celda :valor="$centro->municipio" />
            <x-tabla.celda :valor="$centro->provincia" />
            <x-tabla.celda class="whitespace-nowrap" :valor="$centro->telefono" />
            <x-tabla.celda :valor="$centro->email" />
            <x-tabla.celda tipo="numero" :valor="$centro->numero_total_aula" />

            {{-- CELDA DE ACCIONES (con stopPropagation para que los botones sigan funcionando independientes) --}}
            <td class="px-4 py-3 text-center whitespace-nowrap" onclick="event.stopPropagation()">
                <div class="inline-flex items-center gap-2">
                    <x-tabla.boton-editar :href="route('centro.edit', $centro->id)" />
                    <x-tabla.boton-eliminar :action="route('centro.destroy', $centro->id)" />
                </div>
            </td>
        </x-tabla.fila>
    @endforeach
</x-tabla.tabla>