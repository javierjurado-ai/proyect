<x-tabla.tabla vacio="Todavía no hay cursos guardados." :columnas="['Código', 'Formación', 'Empresa formadora', 'Inicio', 'Fin', 'Turno', 'Horario', 'Aula']">
    @foreach ($cursos as $curso)
        @php
            $turno = $curso->turno->value ?? $curso->turno;
            $colorTurno =
                strtoupper((string) $turno) === 'TARDE' ? 'bg-violet-100 text-violet-800' : 'bg-blue-100 text-blue-800';
        @endphp
        <tr class="cursor-pointer hover:bg-stone-50" onclick="window.location='{{ route('curso.visualizar', $curso) }}'">
            <td class="px-4 py-3 font-mono text-slate-700">{{ $curso->codigo_curso }}</td>
            <td class="px-4 py-3 font-semibold text-slate-900">{{ $curso->formacion?->nombre ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-700">{{ $curso->empresa_formadora ?? '—' }}</td>
            <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                {{ \Illuminate\Support\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                {{ \Illuminate\Support\Carbon::parse($curso->fecha_fin)->format('d/m/Y') }}
            </td>
            <td class="px-4 py-3">
                @if ($turno)
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colorTurno }}">
                        {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($turno)) }}
                    </span>
                @endif
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                @if ($curso->hora_inicio)
                    {{ substr($curso->hora_inicio, 0, 5) }}{{ $curso->hora_final ? ' – ' . substr($curso->hora_final, 0, 5) : '' }}
                @else
                    —
                @endif
            </td>
            <td class="px-4 py-3 text-slate-700">
                {{ $curso->aula ?? '—' }}
                @if ($curso->aula_virtual)
                    <span class="block text-xs text-slate-500">{{ $curso->aula_virtual }}</span>
                @endif
            </td>

            {{-- ACCIONES --}}
            <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="inline-flex items-center gap-2">
                    <x-tabla.boton-editar :href="route('curso.edit', $curso->id)" />
                    <x-tabla.boton-eliminar :action="route('curso.destroy', $curso->id)" />
                </div>
            </td>
        </tr>
    @endforeach
</x-tabla.tabla>
