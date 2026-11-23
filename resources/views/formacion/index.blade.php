@php
    $total = method_exists($formaciones, 'total') ? $formaciones->total() : $formaciones->count();
@endphp

<x-interfaz.layout.app titulo="Formaciones" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Formaciones" subtitulo="{{ $total }} registrados">
        <x-slot:acciones>
            <a href="{{ route('formacion.crearFormulario') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nueva Formacion
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>


    <x-interfaz.panel.tarjeta titulo="Listado de formaciones">
        @include('formacion.tabla')

        @if (method_exists($formaciones, 'links'))
            <div class="mt-4">{{ $formaciones->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>