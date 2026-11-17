<x-tabla.pagina titulo="Centros">
    <x-tabla.tarjeta titulo="Centros" subtitulo="{{ $centros->total() }} registrados">
        <x-slot:acciones>
            <x-tabla.boton href="{{ route('centro.crearFormulario') }}">Nuevo centro</x-tabla.nuevo>
        </x-slot:acciones>

        <x-tabla.tabla :paginacion="$centros" vacio="Todavía no hay centros guardados."
            :columnas="['Nombre', 'Tipo', 'Empresa', 'Municipio', 'Provincia', 'Teléfono', 'Email', ['Aulas', 'right']]">
            @foreach ($centros as $c)
                <x-tabla.fila>
                    <x-tabla.celda tipo="destacado">
                        {{ $c->nombre }}
                        <span class="block text-xs font-normal text-slate-500">{{ $c->direccion }} · {{ $c->codigo_postal }}</span>
                    </x-tabla.celda>
                    <x-tabla.celda tipo="etiqueta" color="blue" :valor="$c->tipo_centro" />
                    <x-tabla.celda :valor="$c->empresa?->nombre" />
                    <x-tabla.celda :valor="$c->municipio" />
                    <x-tabla.celda :valor="$c->provincia" />
                    <x-tabla.celda class="whitespace-nowrap" :valor="$c->telefono" />
                    <x-tabla.celda :valor="$c->email" />
                    <x-tabla.celda tipo="numero" :valor="$c->numero_total_aula" />
                </x-tabla.fila>
            @endforeach
        </x-tabla.tabla>
    </x-tabla.tarjeta>
</x-tabla.pagina>