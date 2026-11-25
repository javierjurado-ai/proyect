@php
    $textoMov = ['SALIDA_CURSO' => 'Salida a curso', 'DEVOLUCION_CURSO' => 'Devolución de curso', 'TRASPASO' => 'Traspaso', 'ENTRADA_COMPRA' => 'Entrada por compra', 'AJUSTE' => 'Ajuste', 'BAJA' => 'Baja'];
    $colorMov = ['SALIDA_CURSO' => 'ambar', 'DEVOLUCION_CURSO' => 'azul', 'TRASPASO' => 'violeta', 'ENTRADA_COMPRA' => 'verde', 'AJUSTE' => 'gris', 'BAJA' => 'rojo'];
@endphp

<x-interfaz.layout.app titulo="Almacén y artículos" :rol="$rol" :usuario="$nombre">

    <x-interfaz.panel.cabecera titulo="Almacén y artículos"
        :subtitulo="$almacen ? 'Stock de ' . $almacen->nombre . ' y del resto de almacenes' : 'No hay almacenes dados de alta'">
        <x-slot:acciones>
            <a href="{{ route('traspaso.create', ['destino' => $almacen?->id]) }}" class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">Pedir a otro almacén</a>
            @if (in_array($rol, ['ADMINISTRADOR', 'COMERCIAL', 'LOGISTA']))
                <a href="{{ route('compra.create', ['almacen_id' => $almacen?->id]) }}" class="inline-flex items-center rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white hover:bg-marca-900">+ Registrar compra</a>
            @endif
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    @if ($almacenes->count() > 1)
        <form method="GET" action="{{ route('almacen.index') }}" class="mb-6 flex items-center gap-2 text-sm">
            <label for="almacen_id" class="text-slate-600">Almacén</label>
            <select id="almacen_id" name="almacen_id" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2">
                @foreach ($almacenes as $a)
                    <option value="{{ $a->id }}" @selected($almacen?->id === $a->id)>{{ $a->nombre }}</option>
                @endforeach
            </select>
        </form>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Artículos activos" :valor="$resumen['articulos']" />
        <x-interfaz.panel.metrica titulo="Equipos disponibles aquí" :valor="$resumen['disponibles']" detalle="con nº de serie" />
        <x-interfaz.panel.metrica titulo="Equipos prestados" :valor="$resumen['prestados']" detalle="en cursos ahora mismo" tono="aviso" />
        <x-interfaz.panel.metrica titulo="Bajo mínimo" :valor="$resumen['bajoMinimo']" detalle="sumando todos los almacenes" :tono="$resumen['bajoMinimo'] ? 'alerta' : 'ok'" />
    </div>

    <x-interfaz.panel.tarjeta titulo="Stock por artículo">
        <x-tabla.tabla :columnas="['Artículo', 'Tipo', ['En ' . ($almacen?->nombre ?? 'este almacén'), 'right'], ['Total', 'right'], ['Prestados', 'right'], ['Mínimo', 'right']]" vacio="No hay artículos dados de alta.">
            @foreach ($articulos as $fila)
                @php $a = $fila['articulo']; @endphp
                <tr class="hover:bg-stone-50">
                    <td class="px-4 py-3">
                        <span class="font-semibold">{{ $a->nombre }}</span>
                        <p class="text-xs text-slate-500">{{ $a->codigo }}{{ $a->controla_serie?->value === 'SI' ? ' · con nº de serie' : '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ ucfirst(strtolower(str_replace('_', ' ', $a->tipo?->value ?? ''))) }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $fila['aqui'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $fila['total'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $fila['prestados'] ?: '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($fila['bajoMinimo'])
                            <x-interfaz.panel.etiqueta color="rojo">{{ $a->stock_minimo }} · reponer</x-interfaz.panel.etiqueta>
                        @else
                            <span class="text-slate-500">{{ $a->stock_minimo ?: '—' }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-tabla.tabla>
    </x-interfaz.panel.tarjeta>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-interfaz.panel.tarjeta titulo="Equipos prestados por curso">
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

        <x-interfaz.panel.tarjeta titulo="Últimos movimientos">
            @if ($movimientos->isEmpty())
                <p class="text-sm text-slate-500">Todavía no hay movimientos en este almacén.</p>
            @else
                <ul class="flex flex-col gap-3 text-sm">
                    @foreach ($movimientos as $m)
                        <li class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">
                                    {{ $m->lineas->groupBy('articulo_id')->map(fn ($g) => $g->sum('cantidad') . ' ' . mb_strtolower($g->first()->articulo?->nombre))->implode(', ') }}
                                </p>
                                <p class="text-slate-500">
                                    {{ $m->fecha->format('d/m/Y') }}
                                    @if ($m->tipo->value === 'TRASPASO')
                                        · {{ $m->almacenOrigen?->nombre }} → {{ $m->almacenDestino?->nombre }}
                                    @elseif ($m->curso)
                                        · {{ $m->curso->formacion?->nombre }}
                                    @endif
                                    {{ $m->referencia ? ' · ' . $m->referencia : '' }}
                                </p>
                            </div>
                            <x-interfaz.panel.etiqueta :color="$colorMov[$m->tipo->value] ?? 'gris'">{{ $textoMov[$m->tipo->value] ?? $m->tipo->value }}</x-interfaz.panel.etiqueta>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-interfaz.panel.tarjeta>
    </div>

</x-interfaz.layout.app>
