@php
    $traspaso = $peticion->esTraspaso();
    $curso = $peticion->curso;
    $nombreCurso = $curso?->formacion?->nombre ?? ($curso ? 'Curso ' . $curso->codigo_accion . '/' . $curso->codigo_grupo : null);
    $titulo = $traspaso
        ? 'Traspaso #' . $peticion->id . ' · ' . ($peticion->almacen?->nombre) . ' → ' . ($peticion->almacenDestino?->nombre)
        : 'Petición #' . $peticion->id . ($nombreCurso ? ' · ' . $nombreCurso : '');
    $colorEstado = ['PENDIENTE' => 'ambar', 'EN_PROCESO' => 'azul', 'ENTREGADA' => 'verde', 'DEVUELTA' => 'gris', 'COMPLETADA' => 'verde', 'CANCELADA' => 'rojo'];
    $textoEstado = ['PENDIENTE' => 'Pendiente', 'EN_PROCESO' => 'Servida en parte', 'ENTREGADA' => 'Entregada', 'DEVUELTA' => 'Devuelta', 'COMPLETADA' => 'Completada', 'CANCELADA' => 'Cancelada'];
    $textoMov = ['SALIDA_CURSO' => 'Salida a curso', 'DEVOLUCION_CURSO' => 'Devolución', 'TRASPASO' => 'Traspaso', 'ENTRADA_COMPRA' => 'Entrada', 'AJUSTE' => 'Ajuste', 'BAJA' => 'Baja'];
    $hayFalta = $lineas->sum('falta') > 0;
@endphp

<x-interfaz.layout.app :titulo="$traspaso ? 'Traspaso' : 'Petición de material'" :rol="$rol" :usuario="$nombre">

    <a href="{{ $traspaso ? route('traspaso.index') : route('pedidosLogistica.index') }}" class="mb-4 inline-block text-sm font-semibold text-marca-900 hover:underline">
        ← {{ $traspaso ? 'Traspasos entre almacenes' : 'Pedidos de material' }}
    </a>

    <x-interfaz.panel.cabecera :titulo="$titulo"
        :subtitulo="'Pedida por ' . ($peticion->solicitante ? trim($peticion->solicitante->nombre . ' ' . $peticion->solicitante->primer_apellido) : '—') . ' el ' . $peticion->fecha->format('d/m/Y')">
        <x-slot:acciones>
            <x-interfaz.panel.etiqueta :color="$colorEstado[$peticion->estado->value] ?? 'gris'" class="px-3 py-1 text-sm">
                {{ $textoEstado[$peticion->estado->value] ?? $peticion->estado->value }}
            </x-interfaz.panel.etiqueta>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    {{-- Datos principales --}}
    <dl class="mb-6 grid gap-px overflow-hidden rounded-xl border border-stone-200 bg-stone-200 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white px-4 py-3"><dt class="text-xs text-slate-500">Sale de</dt><dd class="mt-1 text-sm font-semibold">{{ $peticion->almacen?->nombre ?? '—' }}</dd></div>
        <div class="bg-white px-4 py-3"><dt class="text-xs text-slate-500">Va a</dt><dd class="mt-1 text-sm font-semibold">{{ $traspaso ? $peticion->almacenDestino?->nombre : ($peticion->centro?->nombre ?? '—') }}</dd></div>
        <div class="bg-white px-4 py-3"><dt class="text-xs text-slate-500">Se necesita</dt><dd class="mt-1 text-sm font-semibold">{{ $peticion->fecha_necesaria?->format('d/m/Y') ?? 'Sin fecha' }}</dd></div>
        <div class="bg-white px-4 py-3">
            <dt class="text-xs text-slate-500">{{ $traspaso ? 'Para el curso' : 'Devolución prevista' }}</dt>
            <dd class="mt-1 text-sm font-semibold">{{ $traspaso ? ($nombreCurso ?? '—') : ($peticion->fecha_devolucion_prevista?->format('d/m/Y') ?? '—') }}</dd>
        </div>
    </dl>

    @if ($peticion->observaciones)
        <p class="mb-6 rounded-lg bg-stone-200/60 px-4 py-3 text-sm text-slate-700"><strong>Nota:</strong> {{ $peticion->observaciones }}</p>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Material --}}
        <div class="flex flex-col gap-6 lg:col-span-2">
            <x-interfaz.panel.tarjeta titulo="Material solicitado">
                <x-tabla.tabla :columnas="['Artículo', ['Pedido', 'right'], ['Servido', 'right'], ['Devuelto', 'right'], ['En ' . ($peticion->almacen?->nombre ?? 'almacén'), 'right']]">
                    @foreach ($lineas as $l)
                        @php $p = $l['producto']; @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <span class="font-semibold">{{ $p->articulo?->nombre ?? $p->producto }}</span>
                                <p class="text-xs text-slate-500">{{ $p->articulo?->codigo }}{{ $p->articulo?->retornable?->value === 'SI' ? ' · se devuelve' : ' · no se devuelve' }}</p>
                            </td>
                            <td class="px-4 py-3 text-right">{{ $p->cantidad }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $p->cantidad_servida }}</td>
                            <td class="px-4 py-3 text-right">{{ $traspaso ? '—' : $p->cantidad_devuelta }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-semibold {{ $l['falta'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                    {{ $l['enAlmacen'] }}{{ $l['falta'] > 0 ? ' · faltan ' . $l['falta'] : '' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </x-tabla.tabla>
            </x-interfaz.panel.tarjeta>

            <x-interfaz.panel.tarjeta titulo="Historial">
                @if ($peticion->movimientos->isEmpty())
                    <p class="text-sm text-slate-500">Todavía no ha salido ni vuelto nada.</p>
                @else
                    <ol class="flex flex-col gap-3 text-sm">
                        @foreach ($peticion->movimientos->sortByDesc('fecha') as $m)
                            <li class="flex gap-3">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $m->tipo->value === 'DEVOLUCION_CURSO' ? 'bg-sky-600' : 'bg-emerald-600' }}"></span>
                                <div>
                                    <strong>{{ $textoMov[$m->tipo->value] ?? $m->tipo->value }}</strong>
                                    · {{ $m->lineas->groupBy('articulo_id')->map(fn ($g) => $g->sum('cantidad') . ' ' . mb_strtolower($g->first()->articulo?->nombre))->implode(', ') }}
                                    @if ($m->lineas->whereNotNull('articulo_serie_id')->isNotEmpty())
                                        <span class="text-slate-500">({{ $m->lineas->pluck('unidad.numero_serie')->filter()->implode(', ') }})</span>
                                    @endif
                                    <p class="text-slate-500">{{ $m->fecha->format('d/m/Y H:i') }} · {{ $m->persona ? trim($m->persona->nombre . ' ' . $m->persona->primer_apellido) : '' }}{{ $m->referencia ? ' · ' . $m->referencia : '' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-interfaz.panel.tarjeta>
        </div>

        {{-- Acciones --}}
        <div class="flex flex-col gap-6">
            @if ($puedeServir)
                <x-interfaz.panel.tarjeta :titulo="$traspaso ? 'Enviar traspaso' : 'Servir material'">
                    <p class="mb-4 text-sm text-slate-600">
                        {{ $traspaso
                            ? 'El material sale de ' . $peticion->almacen?->nombre . ' y entra en ' . $peticion->almacenDestino?->nombre . ' al confirmar.'
                            : 'Se descuenta del almacén y los equipos quedan prestados al curso hasta que se devuelvan.' }}
                    </p>

                    @if ($hayFalta)
                        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-sm text-red-800">
                            Falta stock en {{ $peticion->almacen?->nombre }}. Puedes servir lo que hay y el resto más tarde.
                            @foreach ($lineas->where('falta', '>', 0) as $l)
                                @foreach ($l['otrosAlmacenes'] as $s)
                                    @if (! $traspaso)
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span><strong>{{ $s->almacen?->nombre }}</strong> tiene {{ $s->cantidad }} de {{ mb_strtolower($l['producto']->articulo?->nombre) }}.</span>
                                            <a href="{{ route('traspaso.create', ['origen' => $s->almacen_id, 'destino' => $peticion->almacen_id, 'articulo_id' => $l['producto']->articulo_id, 'cantidad' => min($l['falta'], $s->cantidad), 'curso_id' => $peticion->curso_id]) }}"
                                                class="inline-flex items-center rounded-lg bg-red-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-900">
                                                Pedir {{ min($l['falta'], $s->cantidad) }} a {{ $s->almacen?->nombre }}
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('pedidosLogistica.servir', $peticion->id) }}" class="flex flex-col gap-4">
                        @csrf
                        @foreach ($lineas->where('porServir', '>', 0)->values() as $i => $l)
                            @php $p = $l['producto']; @endphp
                            <input type="hidden" name="lineas[{{ $i }}][peticion_producto_id]" value="{{ $p->id }}">
                            <div>
                                <label for="servir-{{ $p->id }}" class="mb-1 block text-sm font-medium text-slate-700">
                                    {{ $p->articulo?->nombre ?? $p->producto }} <span class="font-normal text-slate-500">· quedan {{ $l['porServir'] }}</span>
                                </label>
                                <input id="servir-{{ $p->id }}" type="number" min="0" max="{{ $l['porServir'] }}" name="lineas[{{ $i }}][cantidad]"
                                    value="{{ old("lineas.$i.cantidad", min($l['porServir'], $l['enAlmacen'])) }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2">
                                @if ($p->articulo?->controla_serie?->value === 'SI')
                                    <p class="mt-1 text-xs text-slate-500">Se cogen automáticamente las unidades disponibles por nº de serie.</p>
                                @endif
                            </div>
                        @endforeach
                        <x-formulario.entrada name="transportista" label="Transportista" placeholder="Opcional" />
                        <x-formulario.entrada name="seguimiento" label="Nº de seguimiento" placeholder="Opcional" />
                        <button type="submit" class="rounded-lg bg-marca-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-marca-900">
                            {{ $traspaso ? 'Enviar' : 'Confirmar salida' }}
                        </button>
                    </form>
                </x-interfaz.panel.tarjeta>
            @endif

            @if ($puedeDevolver)
                <x-interfaz.panel.tarjeta titulo="Registrar devolución">
                    <p class="mb-4 text-sm text-slate-600">Lo que vuelve entra otra vez en el stock de {{ $peticion->almacen?->nombre }}. Los fungibles no se devuelven.</p>
                    <form method="POST" action="{{ route('pedidosLogistica.devolver', $peticion->id) }}" class="flex flex-col gap-4">
                        @csrf
                        @foreach ($lineas->where('porDevolver', '>', 0)->values() as $i => $l)
                            @php $p = $l['producto']; @endphp
                            <input type="hidden" name="lineas[{{ $i }}][peticion_producto_id]" value="{{ $p->id }}">
                            <div>
                                <label for="devolver-{{ $p->id }}" class="mb-1 block text-sm font-medium text-slate-700">
                                    {{ $p->articulo?->nombre }} <span class="font-normal text-slate-500">· fuera {{ $l['porDevolver'] }}</span>
                                </label>
                                <input id="devolver-{{ $p->id }}" type="number" min="0" max="{{ $l['porDevolver'] }}" name="lineas[{{ $i }}][cantidad]"
                                    value="{{ $l['porDevolver'] }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                            </div>
                        @endforeach
                        <button type="submit" class="rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-marca-900 hover:bg-stone-50">
                            Confirmar devolución
                        </button>
                    </form>
                </x-interfaz.panel.tarjeta>
            @endif

            @if (! $puedeServir && ! $puedeDevolver)
                <x-interfaz.panel.tarjeta titulo="Sin acciones pendientes">
                    <p class="text-sm text-slate-600">{{ $traspaso ? 'El traspaso está completado.' : 'No queda nada por servir ni por devolver.' }}</p>
                </x-interfaz.panel.tarjeta>
            @endif
        </div>
    </div>

</x-interfaz.layout.app>
