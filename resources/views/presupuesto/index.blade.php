@php
    $total = method_exists($presupuestos, 'total') ? $presupuestos->total() : $presupuestos->count();
@endphp

<x-interfaz.layout.app titulo="Presupuestos" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Presupuestos" subtitulo="{{ $total }} registrados">
        <x-slot:acciones>
            <a href="{{ route('presupuesto.create') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nuevo presupuesto
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>



    <x-interfaz.panel.tarjeta titulo="Listado de presupuestos">
        @include('presupuesto.tabla')

        @if (method_exists($presupuestos, 'links'))
            <div class="mt-4">{{ $presupuestos->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>