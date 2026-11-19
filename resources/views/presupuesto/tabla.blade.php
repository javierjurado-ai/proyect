<x-tabla.tabla vacio="Todavía no hay presupuestos guardados."
    :columnas="['Formación', 'Ubicación', ['Alumnos', 'right'], ['Horas', 'right'], ['Importe bonificado', 'right'], 'Fecha bonificación']">
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
        </x-tabla.fila>
    @endforeach
</x-tabla.tabla>