@php
    $total = method_exists($alumnos, 'total') ? $alumnos->total() : $alumnos->count();
    $tituloCurso = $curso->formacion?->nombre ?? ('Curso ' . $curso->codigo_accion . '/' . $curso->codigo_grupo);
@endphp

<x-interfaz.layout.app titulo="Alumnos · {{ $tituloCurso }}" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Alumnos del curso" subtitulo="{{ $tituloCurso }} · {{ $total }} {{ $total === 1 ? 'alumno' : 'alumnos' }}">
        <x-slot:acciones>
            <a href="{{ route('curso.visualizar', $curso) }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver al curso
            </a>
            <a href="{{ route('persona.create') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nueva persona
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.tarjeta titulo="Listado de alumnos">
        <x-tabla.tabla vacio="Este curso todavía no tiene alumnos."
            :columnas="['Nombre', 'DNI / NIE', 'Email', 'Teléfono', 'Nacimiento', 'Nacionalidad', ['LOPD', 'center']]">
            @foreach ($alumnos as $persona)
                @php $lopd = $persona->LOPD->value ?? $persona->LOPD; @endphp
                <tr class="hover:bg-stone-50">
                    <td class="px-4 py-3 font-semibold text-slate-900">
                        <a href="{{ route('alumno.visualizar', $persona) }}" class="font-semibold text-marca-900 hover:underline" title="Ver ficha del alumno">
                            {{ $persona->nombre }} {{ $persona->primer_apellido }} {{ $persona->segundo_apellido }}
                        </a>
                    </td>
                    <td class="px-4 py-3 font-mono text-slate-700">{{ $persona->DNI ?? $persona->NIE ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $persona->email ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $persona->telefono ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $persona->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $persona->nacionalidad ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $lopd === 'SI' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $lopd === 'SI' ? 'Sí' : 'No' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-tabla.tabla>

        @if (method_exists($alumnos, 'links'))
            <div class="mt-4">{{ $alumnos->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>
