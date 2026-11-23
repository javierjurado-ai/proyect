@php
    $total = method_exists($cursos, 'total') ? $cursos->total() : $cursos->count();
@endphp

<x-interfaz.layout.app titulo="Cursos" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Cursos" subtitulo="{{ $total }} registrados">
        <x-slot:acciones>
            <a href="{{ route('curso.crearFormulario') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nuevo curso
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>


    <x-interfaz.panel.tarjeta titulo="Listado de cursos">
        @include('curso.tabla')

        @if (method_exists($cursos, 'links'))
            <div class="mt-4">{{ $cursos->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>