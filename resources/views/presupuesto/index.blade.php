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

    @if (session('ok'))
        <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <x-interfaz.panel.tarjeta titulo="Listado de presupuestos">
        @include('presupuesto.tabla')

        @if (method_exists($presupuestos, 'links'))
            <div class="mt-4">{{ $presupuestos->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>