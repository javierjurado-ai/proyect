<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docentes</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    @php
        $total = method_exists($docentes, 'total') ? $docentes->total() : $docentes->count();
    @endphp

    <x-tabla.tarjeta titulo="Docentes" subtitulo="{{ $total }} registrados">
        <x-slot:acciones>
            <a href="{{ route('persona.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
                + Nuevo docente
            </a>
        </x-slot:acciones>

        @if (session('ok'))
            <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('ok') }}
            </div>
        @endif

        <x-tabla.tabla vacio="Todavía no hay docentes guardados."
            :columnas="['Nombre', 'DNI / NIE', 'Email', 'Teléfono', 'Nacimiento', 'Nacionalidad', ['LOPD', 'center']]">
            @foreach ($docentes as $docente)
                @php
                    $lopd = $docente->LOPD->value ?? $docente->LOPD;
                @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-800">
                        {{ $docente->nombre }} {{ $docente->primer_apellido }} {{ $docente->segundo_apellido }}
                    </td>
                    <td class="px-4 py-3 font-mono text-slate-700">{{ $docente->DNI ?? $docente->NIE ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $docente->email ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $docente->telefono ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                        {{ $docente->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($docente->fecha_nacimiento)->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $docente->nacionalidad ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if ($lopd === 'SI')
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Sí</span>
                        @else
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">No</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-tabla.tabla>

        @if (method_exists($docentes, 'links'))
            <div class="mt-4">{{ $docentes->links() }}</div>
        @endif
    </x-tabla.tarjeta>
</body>

</html>