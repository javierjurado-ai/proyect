<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formaciones</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-tabla.pagina titulo="Formaciones">
        <x-tabla.tarjeta titulo="Formaciones" subtitulo="{{ $formaciones->total() }} registradas">
            <x-slot:acciones>
                <a href="{{ route('formacion.crearFormulario') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
                    + Nueva formación
                </a>
            </x-slot:acciones>

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <x-tabla.tabla vacio="Todavía no hay formaciones guardadas."
                :columnas="['Nombre', ['Código acción', 'right'], 'Modalidad', 'Sector', ['Horas', 'right'], ['Activo', 'center'], ['Acciones', 'center']]">
                @foreach ($formaciones as $f)
                    <x-tabla.fila>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $f->nombre }}</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-700">{{ $f->codigo_accion }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            @php
                                $colores = [
                                    'PRESENCIAL'    => 'bg-blue-100 text-blue-800',
                                    'TELEFORMACION' => 'bg-purple-100 text-purple-800',
                                    'MIXTA'         => 'bg-amber-100 text-amber-800',
                                ];
                                $valor = $f->modalidad?->value;
                            @endphp
                            @if ($valor)
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $colores[$valor] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst(strtolower($valor)) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $f->sector ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">{{ $f->horas_totales ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($f->activo)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Sí</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">No</span>
                            @endif
                        </td>

                        {{-- ACCIONES --}}
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                <x-tabla.boton-editar :href="route('formacion.edit', $f->id)" />
                                <x-tabla.boton-eliminar :action="route('formacion.destroy', $f->id)" />
                            </div>
                        </td>
                    </x-tabla.fila>
                @endforeach
            </x-tabla.tabla>

            <div class="mt-4">{{ $formaciones->links() }}</div>
        </x-tabla.tarjeta>
    </x-tabla.pagina>
</body>

</html>