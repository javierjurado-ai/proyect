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
    <x-tabla.pagina titulo="Empresas">
        <x-tabla.tarjeta titulo="Empresas" subtitulo="{{ $empresas->total() }} registradas">
            <x-slot:acciones>
                <a href="{{ route('empresa.create') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
                    + Nueva empresa
                </a>
            </x-slot:acciones>

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <x-tabla.tabla vacio="Todavía no hay empresas guardadas."
                :columnas="['CIF', 'Nombre', ['Total trabajadores', 'right'], ['Acciones', 'center']]">
                @foreach ($empresas as $e)
                    <x-tabla.fila>
                        <td class="px-4 py-3 font-mono text-slate-700">{{ $e->cif }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $e->nombre }}</td>
                        <x-tabla.celda tipo="numero" :valor="$e->total_trabajadores" />

                        {{-- ACCIONES --}}
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                <x-tabla.boton-editar :href="route('empresa.editar', $e->id)" />
                                <x-tabla.boton-eliminar :action="route('empresa.eliminar', $e->id)" />
                            </div>
                        </td>
                    </x-tabla.fila>
                @endforeach
            </x-tabla.tabla>

            <div class="mt-4">{{ $empresas->links() }}</div>
        </x-tabla.tarjeta>
    </x-tabla.pagina>
</body>

</html>