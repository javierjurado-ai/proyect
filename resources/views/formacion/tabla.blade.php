<x-tabla.tabla vacio="Todavía no hay formaciones guardadas."
    :columnas="['Código acción', 'Nombre', 'Modalidad', 'Sector', 'Horas', 'Activo']">
    @foreach ($formaciones as $formacion)
        @php
            $modalidad = $formacion->modalidad->value ?? $formacion->modalidad;
            $colorModalidad = match (strtoupper((string) $modalidad)) {
                'PRESENCIAL'    => 'bg-blue-100 text-blue-800',
                'TELEFORMACION' => 'bg-violet-100 text-violet-800',
                'MIXTA'         => 'bg-amber-100 text-amber-800',
                default         => 'bg-stone-100 text-slate-700',
            };
        @endphp
        <tr class="hover:bg-stone-50">
            <td class="px-4 py-3 font-mono text-slate-700">{{ $formacion->codigo_accion }}</td>
            <td class="px-4 py-3 font-semibold text-slate-900">{{ $formacion->nombre }}</td>
            <td class="px-4 py-3">
                @if ($modalidad)
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colorModalidad }}">
                        {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($modalidad)) }}
                    </span>
                @endif
            </td>
            <td class="px-4 py-3 text-slate-700">{{ $formacion->sector ?? '—' }}</td>
            <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $formacion->horas_totales ?? '—' }}</td>
            <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $formacion->activo ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-slate-600' }}">
                    {{ $formacion->activo ? 'Sí' : 'No' }}
                </span>
            </td>
        </tr>
    @endforeach
</x-tabla.tabla>