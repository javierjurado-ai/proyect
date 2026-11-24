@php
    $total = method_exists($centros, 'total') ? $centros->total() : $centros->count();
@endphp

<x-interfaz.layout.app titulo="Pedidos Logística" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Centros" subtitulo="{{ $total }} registrados">
        <x-slot:acciones>
            <a href="{{ route('centro.crearFormulario') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nuevo centro
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>


    <x-interfaz.panel.tarjeta titulo="Listado de pedidos">
        
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>