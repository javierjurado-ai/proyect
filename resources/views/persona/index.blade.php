@php
    $total = method_exists($personas, 'total') ? $personas->total() : $personas->count();
@endphp

<x-interfaz.layout.app titulo="Usuarios" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Usuario y Roles" subtitulo="{{ $total }} registrados">
        <x-slot:acciones>
            <a href="{{ route('persona.crearFormulario') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nueva Persona
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if (session('ok'))
        <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <x-interfaz.panel.tarjeta titulo="Listado de cursos">
        @include('persona.tabla')

        @if (method_exists($personas, 'links'))
            <div class="mt-4">{{ $personas->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>