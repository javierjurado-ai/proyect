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

    @if (session('ok'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Filtro por tipo: solo si el rol ve más de uno --}}
    @if (count($tipos) > 1)
        <div class="mb-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ route('empresa.index') }}"
               class="rounded-full px-3 py-1 font-semibold {{ $tipo === '' ? 'bg-marca-800 text-white' : 'border border-stone-300 bg-white text-slate-700 hover:bg-stone-50' }}">Todas</a>
            @foreach ($tipos as $valor => $texto)
                <a href="{{ route('empresa.index', ['tipo' => $valor]) }}"
                   class="rounded-full px-3 py-1 font-semibold {{ $tipo === $valor ? 'bg-marca-800 text-white' : 'border border-stone-300 bg-white text-slate-700 hover:bg-stone-50' }}">{{ $texto }}s</a>
            @endforeach
        </div>
    @endif

    <x-interfaz.panel.tarjeta titulo="Listado de empresas">
        @include('empresa.tabla')

        @if (method_exists($empresas, 'links'))
            <div class="mt-4">{{ $empresas->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>