@php
    $total = method_exists($empresas, 'total') ? $empresas->total() : $empresas->count();
@endphp

<x-interfaz.layout.app titulo="Empresas" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Empresas" subtitulo="{{ $total }} registradas">
        <x-slot:acciones>
            <a href="{{ route('empresa.create') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nueva empresa
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.tarjeta titulo="Listado de empresas">
        @include('empresa.tabla')

        @if (method_exists($empresas, 'links'))
            <div class="mt-4">{{ $empresas->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>