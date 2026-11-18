<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formaciones</title>
    <script>
        const expira = Number(sessionStorage.getItem('expira')); 

        if (!token || Date.now() / 1000 > expira) {
            sessionStorage.clear();
            window.location.href = '/persona/iniciarSesion';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-tabla.tarjeta titulo="Formaciones" subtitulo="{{ $formaciones->total() }} registradas">
        <x-slot:acciones>
            <a href="{{ route('formacion.crearFormulario') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
                + Nueva formación
            </a>
        </x-slot:acciones>

        @if (session('ok'))
            <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('ok') }}
            </div>
        @endif

        <x-tabla.tabla vacio="Todavía no hay formaciones guardadas."
            :columnas="['Nombre', ['Código acción', 'right'], 'Modalidad', 'Sector', ['Horas', 'right'], ['Activo', 'center']]">
            @foreach ($formaciones as $f)
                <tr class="hover:bg-slate-50">
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
                </tr>
            @endforeach
        </x-tabla.tabla>

        <div class="mt-4">{{ $formaciones->links() }}</div>
    </x-tabla.tarjeta>
</body>

</html>