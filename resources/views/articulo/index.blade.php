<x-interfaz.layout.app titulo="Artículos" :rol="$rol" :usuario="$nombre">

    <x-interfaz.panel.cabecera titulo="Artículos" subtitulo="Catálogo de material y stock total sumando todos los almacenes">
        <x-slot:acciones>
            <a href="{{ route('compra.create') }}" class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">Registrar compra</a>
            <a href="{{ route('articulo.create') }}" class="inline-flex items-center rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white hover:bg-marca-900">+ Nuevo artículo</a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Artículos activos" :valor="$resumen['activos']" />
        <x-interfaz.panel.metrica titulo="Con nº de serie" :valor="$resumen['conSerie']" detalle="se controlan unidad a unidad" />
        <x-interfaz.panel.metrica titulo="Equipos prestados" :valor="$resumen['prestados']" detalle="en cursos ahora mismo" tono="aviso" />
        <x-interfaz.panel.metrica titulo="Bajo mínimo" :valor="$resumen['bajoMinimo']" detalle="sumando todos los almacenes" :tono="$resumen['bajoMinimo'] ? 'alerta' : 'ok'" />
    </div>

    <form method="GET" action="{{ route('articulo.index') }}" class="mb-4 flex flex-wrap items-end gap-3 text-sm">
        <div>
            <label for="buscar" class="mb-1 block text-slate-600">Buscar</label>
            <input id="buscar" name="buscar" value="{{ $buscar }}" placeholder="Nombre o código"
                class="w-56 rounded-lg border border-slate-300 bg-white px-3 py-2">
        </div>
        <div>
            <label for="tipo" class="mb-1 block text-slate-600">Tipo</label>
            <select id="tipo" name="tipo" class="rounded-lg border border-slate-300 bg-white px-3 py-2">
                <option value="">Todos</option>
                @foreach ($tipos as $valor => $texto)
                    <option value="{{ $valor }}" @selected($tipo === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="estado" class="mb-1 block text-slate-600">Estado</label>
            <select id="estado" name="estado" class="rounded-lg border border-slate-300 bg-white px-3 py-2">
                <option value="activos" @selected($estado === 'activos')>Activos</option>
                <option value="inactivos" @selected($estado === 'inactivos')>Inactivos</option>
                <option value="todos" @selected($estado === 'todos')>Todos</option>
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-marca-800 px-4 py-2 font-semibold text-white hover:bg-marca-900">Filtrar</button>
        @if ($buscar !== '' || $tipo !== '' || $estado !== 'activos')
            <a href="{{ route('articulo.index') }}" class="px-2 py-2 text-slate-600 hover:underline">Quitar filtros</a>
        @endif
    </form>

    <x-interfaz.panel.tarjeta titulo="Catálogo">
        <x-tabla.tabla :columnas="[['Acciones', 'center'], 'Artículo', 'Tipo', ['Stock total', 'right'], ['Prestados', 'right'], ['Mínimo', 'right'], ['Estado', 'center']]"
            vacio="No hay artículos con esos filtros.">
            @foreach ($articulos as $fila)
                @php $a = $fila['articulo']; @endphp
                <tr class="hover:bg-stone-50 {{ $a->activo ? '' : 'text-slate-400' }}">
                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-flex items-center gap-2">
                            <x-tabla.boton-editar :href="route('articulo.edit', $a->id)" />
                            <x-tabla.boton-eliminar :action="route('articulo.destroy', $a->id)" mensaje="¿Eliminar este artículo? Solo se puede si nunca se ha movido." />
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('articulo.visualizar', $a->id) }}" class="font-semibold text-marca-900 hover:underline">{{ $a->nombre }}</a>
                        <p class="text-xs text-slate-500">{{ $a->codigo }}{{ $a->controla_serie?->value === 'SI' ? ' · con nº de serie' : '' }}</p>
                    </td>
                    <td class="px-4 py-3">{{ ucfirst(strtolower(str_replace('_', ' ', $a->tipo?->value ?? ''))) }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $fila['total'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $fila['prestados'] ?: '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($fila['bajoMinimo'])
                            <x-interfaz.panel.etiqueta color="rojo">{{ $a->stock_minimo }} · reponer</x-interfaz.panel.etiqueta>
                        @else
                            <span class="text-slate-500">{{ $a->stock_minimo ?: '—' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-interfaz.panel.etiqueta :color="$a->activo ? 'verde' : 'gris'">{{ $a->activo ? 'Activo' : 'Inactivo' }}</x-interfaz.panel.etiqueta>
                    </td>
                </tr>
            @endforeach
        </x-tabla.tabla>
    </x-interfaz.panel.tarjeta>

    <x-interfaz.panel.tarjeta titulo="Equipos prestados por curso" class="mt-6">
        @if ($prestadosPorCurso->isEmpty())
            <p class="text-sm text-slate-500">Ahora mismo no hay ningún equipo prestado.</p>
        @else
            <ul class="flex flex-col gap-3">
                @foreach ($prestadosPorCurso as $cursoId => $unidades)
                    @php $c = $unidades->first()->curso; @endphp
                    <x-interfaz.panel.elemento
                        :nombre="$c?->formacion?->nombre ?? ($c ? 'Curso ' . $c->codigo_accion : 'Sin curso')"
                        :detalle="$unidades->groupBy('articulo_id')->map(fn ($g) => $g->count() . ' ' . mb_strtolower($g->first()->articulo?->nombre) . ' (' . $g->pluck('numero_serie')->implode(', ') . ')')->implode(' · ')"
                        :etiqueta="$c?->fecha_fin ? 'Hasta ' . $c->fecha_fin->format('d/m') : null"
                        color="ambar" />
                @endforeach
            </ul>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>
