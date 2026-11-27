@php
    $textoMov = ['SALIDA_CURSO' => 'Salida a curso', 'DEVOLUCION_CURSO' => 'Devolución de curso', 'TRASPASO' => 'Traspaso', 'ENTRADA_COMPRA' => 'Entrada por compra', 'AJUSTE' => 'Ajuste', 'BAJA' => 'Baja'];
    $colorMov = ['SALIDA_CURSO' => 'ambar', 'DEVOLUCION_CURSO' => 'azul', 'TRASPASO' => 'violeta', 'ENTRADA_COMPRA' => 'verde', 'AJUSTE' => 'gris', 'BAJA' => 'rojo'];
@endphp

<x-interfaz.layout.app titulo="Almacenes" :rol="$rol" :usuario="$nombre">

    <x-interfaz.panel.cabecera titulo="Almacenes"
        :subtitulo="$almacen ? 'Qué hay en ' . $almacen->nombre . ' y qué ha entrado y salido' : 'No hay almacenes activos'">
        <x-slot:acciones>
            <a href="{{ route('traspaso.create', ['destino' => $almacen?->id]) }}" class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">Pedir a otro almacén</a>
            <a href="{{ route('compra.create', ['almacen_id' => $almacen?->id]) }}" class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">Registrar compra</a>
            <a href="{{ route('almacen.create') }}" class="inline-flex items-center rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white hover:bg-marca-900">+ Nuevo almacén</a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    @if ($almacen)
        <div class="mb-6 flex flex-wrap items-center gap-3 text-sm">
            @if ($almacenes->count() > 1)
                <form method="GET" action="{{ route('almacen.index') }}" class="flex items-center gap-2">
                    <label for="almacen_id" class="text-slate-600">Almacén</label>
                    <select id="almacen_id" name="almacen_id" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2">
                        @foreach ($almacenes as $a)
                            <option value="{{ $a->id }}" @selected($almacen->id === $a->id)>{{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <span class="text-slate-500">{{ $almacen->direccion }}, {{ $almacen->municipio }}</span>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-interfaz.panel.metrica titulo="Artículos con stock" :valor="$resumen['articulos']" detalle="en este almacén" />
            <x-interfaz.panel.metrica titulo="Unidades guardadas" :valor="$resumen['unidades']" detalle="sumando todos los artículos" />
            <x-interfaz.panel.metrica titulo="Equipos disponibles" :valor="$resumen['disponibles']" detalle="con nº de serie" tono="ok" />
            <x-interfaz.panel.metrica titulo="En reparación" :valor="$resumen['enReparacion']" detalle="equipos de este almacén" :tono="$resumen['enReparacion'] ? 'aviso' : 'normal'" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-interfaz.panel.tarjeta :titulo="'Stock en ' . $almacen->nombre">
                <x-tabla.tabla :columnas="['Artículo', 'Tipo', ['Cantidad', 'right'], ['Equipos', 'right']]" vacio="Este almacén está vacío.">
                    @foreach ($stock as $s)
                        @php $a = $s->articulo; @endphp
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('articulo.visualizar', $a->id) }}" class="font-semibold text-marca-900 hover:underline">{{ $a->nombre }}</a>
                                <p class="text-xs text-slate-500">{{ $a->codigo }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ ucfirst(strtolower(str_replace('_', ' ', $a->tipo?->value ?? ''))) }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $s->cantidad }}</td>
                            <td class="px-4 py-3 text-right text-slate-600">
                                @if ($a->controla_serie?->value === 'SI')
                                    {{ $disponibles[$a->id] ?? 0 }} disp.
                                    @if ($enReparacion[$a->id] ?? 0)
                                        · {{ $enReparacion[$a->id] }} en rep.
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-tabla.tabla>
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
    @endif

    <x-interfaz.panel.tarjeta titulo="Todos los almacenes" class="mt-6">
        <x-tabla.tabla :columnas="[['Acciones', 'center'], 'Almacén', 'Ubicación', 'Centro', 'Responsable', ['Unidades', 'right'], ['Estado', 'center']]" vacio="Todavía no hay almacenes. Crea el primero con «+ Nuevo almacén».">
            @foreach ($todos as $a)
                <tr class="hover:bg-stone-50 {{ $a->activo ? '' : 'text-slate-400' }}">
                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-flex items-center gap-2">
                            <x-tabla.boton-editar :href="route('almacen.edit', $a->id)" />
                            <x-tabla.boton-eliminar :action="route('almacen.destroy', $a->id)" mensaje="¿Eliminar este almacén? Solo se puede si nunca ha tenido movimientos." />
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if ($a->activo)
                            <a href="{{ route('almacen.index', ['almacen_id' => $a->id]) }}" class="font-semibold text-marca-900 hover:underline">{{ $a->nombre }}</a>
                        @else
                            <span class="font-semibold">{{ $a->nombre }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $a->municipio }} ({{ $a->provincia }})</td>
                    <td class="px-4 py-3">{{ $a->centro?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $a->responsable ? trim($a->responsable->nombre . ' ' . $a->responsable->primer_apellido) : '—' }}</td>
                    <td class="px-4 py-3 text-right">{{ (int) $a->unidades_total }}</td>
                    <td class="px-4 py-3 text-center">
                        <x-interfaz.panel.etiqueta :color="$a->activo ? 'verde' : 'gris'">{{ $a->activo ? 'Activo' : 'Inactivo' }}</x-interfaz.panel.etiqueta>
                    </td>
                </tr>
            @endforeach
        </x-tabla.tabla>
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>
