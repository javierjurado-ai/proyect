<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuestos</title>
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
    <x-tabla.tarjeta titulo="Presupuestos" subtitulo="{{ $presupuestos->total() }} registrados">
        <x-slot:acciones>
            <a href="{{ route('presupuesto.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
                + Nuevo presupuesto
            </a>
        </x-slot:acciones>

        @if (session('ok'))
            <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('ok') }}
            </div>
        @endif

        <x-tabla.tabla vacio="Todavía no hay presupuestos guardados."
            :columnas="['Formación', 'Ubicación', ['Alumnos', 'right'], ['Horas', 'right'], ['Importe bonificado', 'right'], 'Fecha bonificación']">
            @foreach ($presupuestos as $p)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $p->nombre_formacion }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $p->ubicacion_formacion }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ $p->numero_alumnos }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ $p->horas_totales }}</td>
                   <td class="px-4 py-3 whitespace-nowrap text-right text-slate-700">{{ number_format($p->importe_bonificado, 2, ',', '.') }} €</td>
<td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ \Illuminate\Support\Carbon::parse($p->fecha_bonificado)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </x-tabla.tabla>

        <div class="mt-4">{{ $presupuestos->links() }}</div>
    </x-tabla.tarjeta>
</body>

</html>