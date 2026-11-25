<x-interfaz.layout.app :titulo="'Listado de Aulas'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera :titulo="'Aulas'" subtitulo="Gestión y control de aulas disponibles">
        <x-slot:acciones>
            {{-- Las aulas se crean desde un centro: primero se elige el centro --}}
            <a href="{{ route('centro.index') }}"
               class="inline-flex items-center gap-1 rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                + Nueva aula (elige centro)
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Mensaje de éxito --}}
    @if (session('ok'))
        <div role="status" class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        @if ($aulas->isEmpty())
            <div class="rounded-lg border border-dashed border-stone-300 p-8 text-center text-stone-500">
                Todavía no hay aulas registradas en el sistema.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-stone-700">
                    <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase text-stone-600">
                        <tr>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Curso asignado</th>
                            <th class="px-4 py-3 text-center">Capacidad</th>
                            <th class="px-4 py-3 text-center">Dimensión (m²)</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Uso</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @foreach ($aulas as $aula)
                            <tr class="hover:bg-stone-50">
                                <td class="px-4 py-3 font-medium text-stone-900">{{ $aula->nombre_aula }}</td>
                                <td class="px-4 py-3">
                                    @if ($aula->curso)
                                        {{ $aula->curso->codigo_accion }} / {{ $aula->curso->codigo_grupo }}
                                    @else
                                        <span class="text-stone-400 italic">Sin curso asignado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">{{ $aula->capacidad }} pers.</td>
                                <td class="px-4 py-3 text-center">{{ $aula->dimension ? $aula->dimension . ' m²' : '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold 
                                        {{ $aula->estado->value === 'OCUPADA' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst(strtolower($aula->estado->value)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ ucfirst(strtolower($aula->uso_aula->value)) }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <x-tabla.boton-editar :href="route('aula.edit', $aula->id)" />
                                        <x-tabla.boton-eliminar :action="route('aula.destroy', $aula->id)" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

</x-interfaz.layout.app>