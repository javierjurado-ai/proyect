<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        const expira = Number(sessionStorage.getItem('expira')); 

        if (!token || Date.now() / 1000 > expira) {
            sessionStorage.clear();
            window.location.href = '/persona/iniciarSesion';
        }
    </script>
    <title>Empresas</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-tabla.tarjeta titulo="Empresas" subtitulo="{{ $empresas->total() }} registradas">
        <x-slot:acciones>
            <a href="{{ url('/empresa') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
                + Nueva empresa
            </a>
        </x-slot:acciones>

        @if (session('ok'))
            <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('ok') }}
            </div>
        @endif

        <x-tabla.tabla vacio="Todavía no hay empresas guardadas."
            :columnas="['CIF', 'Nombre', ['Total trabajadores', 'right']]">
            @foreach ($empresas as $e)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-slate-700">{{ $e->cif }}</td>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $e->nombre }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ $e->total_trabajadores ?? '—' }}</td>
                </tr>
            @endforeach
        </x-tabla.tabla>

        <div class="mt-4">{{ $empresas->links() }}</div>
    </x-tabla.tarjeta>
</body>

</html>