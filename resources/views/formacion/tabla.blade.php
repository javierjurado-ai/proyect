<x-tabla.tabla vacio="Todavía no hay formaciones guardadas."
    :columnas="['Código acción', 'Nombre', 'Horas', 'Cursos', 'Activo', ['Acciones', 'center']]">
    @foreach ($formaciones as $formacion)
        <tr class="hover:bg-stone-50">
            <td class="px-4 py-3 font-mono text-slate-700">{{ $formacion->codigo_accion ?? '—' }}</td>
            <td class="px-4 py-3 font-semibold text-slate-900">{{ $formacion->nombre }}</td>
            <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $formacion->horas_totales ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-700">{{ $formacion->cursos_count ?? '—' }}</td>
            <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $formacion->activo ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-slate-600' }}">
                    {{ $formacion->activo ? 'Sí' : 'No' }}
                </span>
            </td>

            {{-- ACCIONES --}}
            <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="inline-flex items-center gap-2">
                    <x-tabla.boton-editar :href="route('formacion.edit', $formacion->id)" />
                    <x-tabla.boton-eliminar :action="route('formacion.destroy', $formacion->id)" />
                </div>
            </td>
        </tr>
    @endforeach
</x-tabla.tabla>
