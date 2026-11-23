@php
    $total = $alumnos->total();
    $hayFiltro = $buscar !== '' || $cursoId;
@endphp

<x-interfaz.layout.app titulo="Alumnos" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Alumnos"
        subtitulo="{{ $total }} {{ $total === 1 ? 'alumno' : 'alumnos' }}{{ $hayFiltro ? ' con este filtro' : ' inscritos en algún curso' }}">
    </x-interfaz.panel.cabecera>

    {{-- Buscador y filtro --}}
    <form method="GET" action="{{ route('alumno.index') }}"
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
            <a href="{{ route('alumno.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">Quitar filtro</a>
        @endif
    </form>

    <x-interfaz.panel.tarjeta titulo="Listado de alumnos">
        <x-tabla.tabla :vacio="$hayFiltro ? 'Ningún alumno coincide con la búsqueda.' : 'Todavía no hay alumnos inscritos en ningún curso.'"
            :columnas="['Alumno', 'DNI / NIE', 'Contacto', 'Empresas', ['Cursos', 'center'], ['Diplomas', 'center']]">
            @foreach ($alumnos as $a)
                <tr class="hover:bg-stone-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('alumno.visualizar', $a) }}" class="font-semibold text-marca-900 hover:underline" title="Ver ficha">
                            {{ $a->primer_apellido }} {{ $a->segundo_apellido }}, {{ $a->nombre }}
                        </a>
                    </td>
                    <td class="px-4 py-3 font-mono text-slate-700">{{ $a->DNI ?: $a->NIE ?: '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">
                        <span class="block">{{ $a->email }}</span>
                        <span class="block text-xs text-slate-500">{{ $a->telefono }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        {{ $a->empresasComoAlumno->pluck('nombre')->unique()->implode(', ') ?: '—' }}
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-slate-900">{{ $a->cursos_count }}</td>
                    <td class="px-4 py-3 text-center text-slate-700">{{ $a->diplomas_count }}</td>
                </tr>
            @endforeach
        </x-tabla.tabla>

        <div class="mt-4">{{ $alumnos->links() }}</div>
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>
