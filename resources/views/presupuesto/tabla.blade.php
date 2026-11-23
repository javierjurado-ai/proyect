<x-tabla.tabla vacio="Todavía no hay presupuestos guardados."
    :columnas="['Formación', 'Ubicación', ['Alumnos', 'right'], ['Horas', 'right'], ['Importe bonificado', 'right'], 'Fecha bonificación', ['Acciones', 'center']]">
    @foreach ($presupuestos as $p)
        <x-tabla.fila>
            <td class="px-4 py-3 font-medium text-slate-800">{{ $p->nombre_formacion ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-700">{{ $p->ubicacion_formacion ?? '—' }}</td>
            <td class="px-4 py-3 text-right text-slate-700">{{ $p->numero_alumnos ?? '—' }}</td>
            <td class="px-4 py-3 text-right text-slate-700">{{ $p->horas_totales ?? '—' }}</td>
            <td class="px-4 py-3 whitespace-nowrap text-right text-slate-700">
                {{ $p->importe_bonificado !== null ? number_format((float) $p->importe_bonificado, 2, ',', '.') . ' €' : '—' }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                {{ $p->fecha_bonificado ? \Illuminate\Support\Carbon::parse($p->fecha_bonificado)->format('d/m/Y') : '—' }}
            </td>

            {{-- ACCIONES --}}
            <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="inline-flex items-center gap-2">
                    <x-tabla.boton-editar :href="route('presupuesto.edit', $p->id)" />
                    <x-tabla.boton-eliminar :action="route('presupuesto.destroy', $p->id)" />
                </div>
            </td>
        </x-tabla.fila>
    @endforeach
</x-tabla.tabla>