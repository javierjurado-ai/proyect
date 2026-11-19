@php
    $total = method_exists($empresas, 'total') ? $empresas->total() : $empresas->count();
@endphp

<x-interfaz.layout.app titulo="Empresas" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Empresas" subtitulo="{{ $total }} registradas">
        <x-slot:acciones>
            <a href="{{ url('/empresa') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nueva empresa
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

    <x-interfaz.panel.tarjeta titulo="Listado de empresas">
        @include('empresa.tabla')

        @if (method_exists($empresas, 'links'))
            <div class="mt-4">{{ $empresas->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>