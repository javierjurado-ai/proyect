@php
    $total = method_exists($cursos, 'total') ? $cursos->total() : $cursos->count();
    $hayFiltro = !empty($fInicio) || !empty($fFin) || !empty($formacionId) || !empty($empresaId) || !empty($modalidad) || !empty($codigoAccion) || !empty($codigoGrupo);
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

    {{-- Formulario con 7 filtros independientes --}}
    {{-- Formulario compacto en una sola fila --}}
    <form method="GET" action="{{ route('curso.crearTablaCurso') }}"
          class="mb-6 flex flex-wrap xl:flex-nowrap items-end gap-2 rounded-xl border border-stone-200 bg-white p-3">
        
        {{-- 1. Fecha Inicio --}}
        <div class="w-36 shrink-0">
            <label for="fecha_inicio" class="mb-1 block text-xs font-medium text-slate-700">Fecha inicio</label>
            <input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ $fInicio ?? '' }}"
                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-marca-700">
        </div>

        {{-- 2. Fecha Fin --}}
        <div class="w-36 shrink-0">
            <label for="fecha_fin" class="mb-1 block text-xs font-medium text-slate-700">Fecha fin</label>
            <input id="fecha_fin" name="fecha_fin" type="date" value="{{ $fFin ?? '' }}"
                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-marca-700">
        </div>

        {{-- 3. Nombre Formación --}}
        <div class="min-w-44 flex-1">
            <label for="formacion" class="mb-1 block text-xs font-medium text-slate-700">Nombre Formación</label>
            <select id="formacion" name="formacion"
                    class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-marca-700">
                <option value="">Todas</option>
                @foreach ($formacionesFiltro ?? [] as $id => $nombreForm)
                    <option value="{{ $id }}" @selected(($formacionId ?? '') == $id)>{{ $nombreForm }}</option>
                @endforeach
            </select>
        </div>

        {{-- 4. Empresa Cliente --}}
        <div class="min-w-40 flex-1">
            <label for="empresa" class="mb-1 block text-xs font-medium text-slate-700">Empresa Cliente</label>
            <select id="empresa" name="empresa"
                    class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-marca-700">
                <option value="">Todas</option>
                @foreach ($empresasFiltro ?? [] as $id => $nombreEmp)
                    <option value="{{ $id }}" @selected(($empresaId ?? '') == $id)>{{ $nombreEmp }}</option>
                @endforeach
            </select>
        </div>

        {{-- 5. Modalidad --}}
        <div class="w-36 shrink-0">
            <label for="modalidad" class="mb-1 block text-xs font-medium text-slate-700">Modalidad</label>
            <select id="modalidad" name="modalidad"
                    class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-marca-700">
                <option value="">Todas</option>
                @foreach ($modalidadesFiltro ?? [] as $val => $text)
                    <option value="{{ $val }}" @selected(($modalidad ?? '') == $val)>{{ $text }}</option>
                @endforeach
            </select>
        </div>

        {{-- 6. Código Acción --}}
        <div class="w-28 shrink-0">
            <label for="codigo_accion" class="mb-1 block text-xs font-medium text-slate-700">Cód. Acción</label>
            <input id="codigo_accion" name="codigo_accion" type="search" value="{{ $codigoAccion ?? '' }}" placeholder="Código..."
                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-marca-700">
        </div>

        {{-- 7. Código Grupo --}}
        <div class="w-28 shrink-0">
            <label for="codigo_grupo" class="mb-1 block text-xs font-medium text-slate-700">Cód. Grupo</label>
            <input id="codigo_grupo" name="codigo_grupo" type="search" value="{{ $codigoGrupo ?? '' }}" placeholder="Grupo..."
                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-marca-700">
        </div>

        {{-- Botones de acción --}}
        <div class="flex shrink-0 items-center gap-2">
            <button type="submit" class="rounded-lg bg-marca-800 px-4 py-1.5 text-sm font-semibold text-white hover:bg-marca-900">Buscar</button>
            @if ($hayFiltro)
                <a href="{{ route('curso.crearTablaCurso') }}" title="Quitar filtros"
                   class="rounded-lg px-2.5 py-1.5 text-sm font-semibold text-slate-700 hover:bg-stone-100">Limpiar</a>
            @endif
        </div>
    </form>

    <x-interfaz.panel.tarjeta titulo="Listado de cursos">
        @include('curso.tabla')

        @if (method_exists($cursos, 'links'))
            <div class="mt-4">{{ $cursos->links() }}</div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>