<section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
    {{-- Cabecera con botón Nueva Aula --}}
    <div class="mb-6 flex items-center justify-between">
        <h3 class="text-base font-semibold text-stone-900">Aulas del centro</h3>
        <a href="{{ route('aula.create', $centro->id) }}"
           class="inline-flex items-center gap-2 rounded-lg bg-marca-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-marca-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Aula
        </a>
    </div>

    @if ($aulas->isEmpty())
        <div class="rounded-lg border border-dashed border-stone-300 p-8 text-center text-stone-500">
            Todavía no hay aulas asociadas a los cursos de este centro.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-700">
                <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase text-stone-600">
                    <tr>
                        <th class="px-4 py-3 text-center">Acciones</th>
                        <th class="px-4 py-3">Nombre Del Aula</th>
                        <th class="px-4 py-3">Nombre Del Curso </th>
                        <th class="px-4 py-3">Fecha Inicio</th>
                        <th class="px-4 py-3">Fecha Fin</th>
                        <th class="px-4 py-3">Turno</th>
                        <th class="px-4 py-3 text-center">Capacidad</th>
                        <th class="px-4 py-3 text-center">Dimensión (m²)</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Uso</th>
                        <th class="px-4 py-3">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @foreach ($aulas as $aula)
                        <tr class="hover:bg-stone-50">
                            {{-- CELDA DE ACCIONES AL INICIO --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap" onclick="event.stopPropagation()">
                                <div class="inline-flex items-center gap-2">
                                    <x-tabla.boton-editar :href="route('aula.edit', $aula->id)" />
                                    <x-tabla.boton-eliminar :action="route('aula.destroy', $aula->id)" />
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium text-stone-900">{{ $aula->nombre_aula }}</td>
                            <td class="px-4 py-3">
                                @if ($aula->curso)
                                    <a href="{{ route('curso.visualizar', $aula->curso->id) }}" class="font-medium text-marca-900 hover:underline">
                                        {{ $aula->curso->formacion?->nombre ?? 'Sin nombre de formación' }}
                                    </a>
                                    <span class="block text-xs text-stone-500">{{ $aula->curso->codigo_accion }} / {{ $aula->curso->codigo_grupo }}</span>                                    
                                @else
                                    <span class="text-stone-400 italic">Sin curso</span>
                                @endif
                            </td> 
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $aula->curso?->fecha_inicio ? \Carbon\Carbon::parse($aula->curso->fecha_inicio)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $aula->curso?->fecha_fin ? \Carbon\Carbon::parse($aula->curso->fecha_fin)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $aula->curso?->turno ?? '—' }}
                            </td>                           
                            <td class="px-4 py-3 text-center">{{ $aula->capacidad }} pers.</td>
                            <td class="px-4 py-3 text-center">{{ $aula->dimension ? $aula->dimension . ' m²' : '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold 
                                    {{ $aula->estado?->value === 'OCUPADA' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst(strtolower($aula->estado?->value ?? '')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ ucfirst(strtolower($aula->uso_aula?->value ?? '')) }}</td>
                            <td class="px-4 py-3">{{ $aula->observaciones ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>