<x-tabla.tabla vacio="Todavía no hay personas guardadas."
    :columnas="['Acciones', 'Nombre', 'DNI / NIE', 'Email', 'Teléfono', 'Género', 'LOPD']">
    @foreach ($personas as $persona)
        @php
            $genero = $persona->genero->value ?? $persona->genero;
            $lopd = $persona->LOPD->value ?? $persona->LOPD;
        @endphp
        <x-tabla.fila>
            {{-- ACCIONES (primera columna) --}}
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="inline-flex items-center gap-2">
                    <x-tabla.boton-editar :href="route('persona.edit', $persona->id)" />
                </div>
            </td>

            <td class="px-4 py-3 font-semibold text-slate-900">
                {{ trim($persona->nombre . ' ' . $persona->primer_apellido . ' ' . $persona->segundo_apellido) }}
            </td>
            <td class="px-4 py-3 font-mono text-slate-700">{{ $persona->DNI ?: $persona->NIE ?: '—' }}</td>
            <td class="px-4 py-3 text-slate-700">{{ $persona->email ?? '—' }}</td>
            <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $persona->telefono ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-700">
                {{ $genero ? \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($genero)) : '—' }}
            </td>
            <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ strtoupper((string) $lopd) === 'SI' ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-slate-600' }}">
                    {{ strtoupper((string) $lopd) === 'SI' ? 'Sí' : 'No' }}
                </span>
            </td>
        </x-tabla.fila>
    @endforeach
</x-tabla.tabla>