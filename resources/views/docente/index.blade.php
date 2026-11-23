@php
    $total = $docentes->total();
    $hayFiltro = $buscar !== '' || $cursoId;
    $horas = fn ($h) => rtrim(rtrim(number_format((float) $h, 2, ',', '.'), '0'), ',');
@endphp

<x-interfaz.layout.app titulo="Docentes" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Docentes"
        subtitulo="{{ $total }} {{ $total === 1 ? 'docente' : 'docentes' }}{{ $hayFiltro ? ' con este filtro' : ' que imparten algún curso' }}">
        <x-slot:acciones>
            <a href="{{ route('persona.create') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nueva persona
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Buscador y filtro --}}
    <form method="GET" action="{{ route('docente.index') }}"
          class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-stone-200 bg-white p-4">
        <div class="min-w-64 flex-1">
            <label for="buscar" class="mb-1 block text-sm font-medium text-slate-700">Buscar</label>
            <input id="buscar" name="buscar" type="search" value="{{ $buscar }}" placeholder="Nombre, apellidos, DNI, NIE o email"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-marca-700">
        </div>
        <div class="min-w-64">
            <label for="curso" class="mb-1 block text-sm font-medium text-slate-700">Curso</label>
            <select id="curso" name="curso"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-marca-700">
                <option value="">Todos los cursos</option>
                @foreach ($cursos as $id => $texto)
                    <option value="{{ $id }}" @selected($cursoId == $id)>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">Buscar</button>
        @if ($hayFiltro)
            <a href="{{ route('docente.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">Quitar filtro</a>
        @endif
    </form>

    <x-interfaz.panel.tarjeta titulo="Listado de docentes">
        <x-tabla.tabla :vacio="$hayFiltro ? 'Ningún docente coincide con la búsqueda.' : 'Todavía no hay docentes asignados a ningún curso.'"
            :columnas="['Docente', 'DNI / NIE', 'Contacto', ['Cursos', 'center'], ['En marcha', 'center'], ['Horas', 'right']]">
            @foreach ($docentes as $d)
                <tr class="hover:bg-stone-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('docente.visualizar', $d) }}" class="font-semibold text-marca-900 hover:underline" title="Ver ficha">
                            {{ $d->primer_apellido }} {{ $d->segundo_apellido }}, {{ $d->nombre }}
                        </a>
                    </td>
                    <td class="px-4 py-3 font-mono text-slate-700">{{ $d->DNI ?: $d->NIE ?: '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">
                        <span class="block">{{ $d->email }}</span>
                        <span class="block text-xs text-slate-500">{{ $d->telefono }}</span>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-slate-900">{{ $d->cursos_count }}</td>
                    <td class="px-4 py-3 text-center">
                        @if ($d->en_marcha_count)
                            <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">{{ $d->en_marcha_count }}</span>
                        @else
                            <span class="text-slate-400">0</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-slate-700">{{ $horas($d->horas_total) }} h</td>
                </tr>
            @endforeach
        </x-tabla.tabla>

        <div class="mt-4">{{ $docentes->links() }}</div>
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>
