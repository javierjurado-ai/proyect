<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centros</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-tabla.pagina titulo="Centros">
        <x-tabla.tarjeta titulo="Centros" subtitulo="{{ $centros->total() }} registrados">
            <x-slot:acciones>
                <a href="{{ route('centro.crearFormulario') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
                    + Nuevo centro
                </a>
            </x-slot:acciones>

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <x-tabla.tabla :paginacion="$centros" vacio="Todavía no hay centros guardados." :columnas="['Nombre', 'Tipo', 'Empresa', 'Municipio', 'Provincia', 'Teléfono', 'Email', ['Aulas', 'right'], ['Acciones', 'center']]">
                @foreach ($centros as $centro)
                    <x-tabla.fila>
                        <x-tabla.celda tipo="destacado">
                            {{ $centro->nombre }}
                            <span class="block text-xs font-normal text-slate-500">{{ $centro->direccion }} ·
                                {{ $centro->codigo_postal }}</span>
                        </x-tabla.celda>
                        <x-tabla.celda tipo="etiqueta" color="blue" :valor="$centro->tipo_centro" />
                        <x-tabla.celda :valor="$centro->empresa?->nombre" />
                        <x-tabla.celda :valor="$centro->municipio" />
                        <x-tabla.celda :valor="$centro->provincia" />
                        <x-tabla.celda class="whitespace-nowrap" :valor="$centro->telefono" />
                        <x-tabla.celda :valor="$centro->email" />
                        <x-tabla.celda tipo="numero" :valor="$centro->numero_total_aula" />

                        {{-- CELDA DE ACCIONES --}}
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                <x-tabla.boton-editar :href="route('centro.edit', $centro->id)" />
                                <x-tabla.boton-eliminar :action="route('centro.destroy', $centro->id)" />
                            </div>
                        </td>
                    </x-tabla.fila>
                @endforeach
            </x-tabla.tabla>
        </x-tabla.tarjeta>
    </x-tabla.pagina>
</body>

</html>