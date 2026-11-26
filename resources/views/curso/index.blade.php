@php
    $total = method_exists($cursos, 'total') ? $cursos->total() : $cursos->count();
    $hayFiltro = !empty($buscar) || !empty($filtroId);
@endphp

<x-interfaz.layout.app titulo="Cursos" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Cursos" subtitulo="{{ $total }} {{ $total === 1 ? 'curso registrado' : 'cursos registrados' }}{{ $hayFiltro ? ' con este filtro' : '' }}">
        <x-slot:acciones>
            <a href="{{ route('curso.crearFormulario') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nuevo curso
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Buscador y filtro (Estilo idéntico al módulo de Alumnos) --}}
    <form method="GET" action="{{ route('curso.crearTablaCurso') }}"
          class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-stone-200 bg-white p-4">
        <div class="min-w-64 flex-1">
            <label for="buscar" class="mb-1 block text-sm font-medium text-slate-700">Buscar por fecha</label>
            <input id="buscar" name="buscar" type="search" value="{{ $buscar ?? '' }}" placeholder="Fecha de inicio o fin (ej. 2026-11-27)"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-marca-700">
        </div>
        <div class="min-w-64">
            <label for="filtro" class="mb-1 block text-sm font-medium text-slate-700">Filtrar por detalle</label>
            <select id="filtro" name="filtro"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-marca-700">
                <option value="">Todos los registros</option>
                @foreach ($opcionesFiltro as $grupo => $opciones)
                    <optgroup label="{{ $grupo }}">
                        @foreach ($opciones as $valor)
                            <option value="{{ $valor }}" @selected(($filtroId ?? '') == $valor)>{{ $valor }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">Buscar</button>
        @if ($hayFiltro)
            <a href="{{ route('curso.crearTablaCurso') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">Quitar filtro</a>
        @endif
    </form>

    <x-interfaz.panel.tarjeta titulo="Listado de cursos">
        @include('curso.tabla')

        @if (method_exists($cursos, 'links'))
            <div class="mt-4">{{ $cursos->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>