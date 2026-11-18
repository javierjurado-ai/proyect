<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    @php
        $total = method_exists($cursos, 'total') ? $cursos->total() : $cursos->count();
    @endphp

    <x-tabla.tarjeta titulo="Cursos" subtitulo="{{ $total }} registrados">
        <x-slot:acciones>
            <a href="{{ route('curso.crearFormulario') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
                + Nuevo curso
            </a>
        </x-slot:acciones>

        <x-tabla.tabla vacio="Todavía no hay cursos guardados."
            :columnas="['Código', 'Formación', 'Empresa formadora', 'Inicio', 'Fin', 'Turno', 'Horario', 'Aula', ['Acciones', 'center']]">
            @foreach ($cursos as $curso)
                @php
                    $turno = $curso->turno->value ?? $curso->turno;
                @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-slate-700">{{ $curso->codigo_curso }}</td>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $curso->formacion?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $curso->empresa_formadora ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                        {{ \Illuminate\Support\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                        {{ \Illuminate\Support\Carbon::parse($curso->fecha_fin)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($turno)
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
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

                    {{-- NUEVA CELDA DE ACCIONES --}}
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <div class="inline-flex items-center gap-2">
                            <x-tabla.boton-editar :href="route('curso.edit', $curso->id)" />
                            <x-tabla.boton-eliminar :action="route('curso.destroy', $curso->id)" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-tabla.tabla>

        @if (method_exists($cursos, 'links'))
            <div class="mt-4">{{ $cursos->links() }}</div>
        @endif
    </x-tabla.tarjeta>
</body>

</html>