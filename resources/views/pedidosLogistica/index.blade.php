@php
    $nombreCurso = fn ($c) => $c?->formacion?->nombre ?? ($c ? 'Curso ' . $c->codigo_accion . '/' . $c->codigo_grupo : 'Sin curso');
    $colorEstado = ['PENDIENTE' => 'ambar', 'EN_PROCESO' => 'azul', 'ENTREGADA' => 'verde', 'DEVUELTA' => 'gris', 'COMPLETADA' => 'gris', 'CANCELADA' => 'rojo'];
    $textoEstado = ['PENDIENTE' => 'Pendiente', 'EN_PROCESO' => 'Servida en parte', 'ENTREGADA' => 'Entregada', 'DEVUELTA' => 'Devuelta', 'COMPLETADA' => 'Completada', 'CANCELADA' => 'Cancelada'];
    $hoy = today();
@endphp

<x-interfaz.layout.app titulo="Pedidos de material" :rol="$rol" :usuario="$nombre" :contadores="$contadores">

    <x-interfaz.panel.cabecera titulo="Pedidos de material"
        subtitulo="Las crean los coordinadores para sus cursos; desde aquí se sirven y se registra la devolución">
        <x-slot:acciones>
            <a href="{{ route('almacen.index') }}" class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">Ver stock</a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    <x-interfaz.panel.tarjeta titulo="Peticiones de cursos">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <nav aria-label="Filtrar por estado" class="flex flex-wrap gap-1">
                @foreach ($pestanas as $clave => [$texto, $cuenta])
                    <a href="{{ route('pedidosLogistica.index', array_filter(['estado' => $clave, 'almacen_id' => request('almacen_id')])) }}"
                        @if ($filtro === $clave) aria-current="page" @endif
                        class="rounded-lg px-3 py-2 text-sm font-semibold {{ $filtro === $clave ? 'bg-marca-950 text-white' : 'text-slate-700 hover:bg-stone-100' }}">
                        {{ $texto }} <span class="opacity-70">{{ $cuenta }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('pedidosLogistica.index') }}" class="flex items-center gap-2 text-sm">
                <input type="hidden" name="estado" value="{{ $filtro }}">
                <label for="almacen_id" class="text-slate-600">Almacén</label>
                <select id="almacen_id" name="almacen_id" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2">
                    <option value="">Todos</option>
                    @foreach ($almacenes as $a)
                        <option value="{{ $a->id }}" @selected((string) request('almacen_id') === (string) $a->id)>{{ $a->nombre }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <x-tabla.tabla :columnas="['Curso', 'Destino', 'Se necesita', 'Material', 'Estado', ['Acción', 'right']]" vacio="No hay peticiones en este estado.">
            @foreach ($peticiones as $p)
                @php
                    $dias = $p->fecha_necesaria ? $hoy->diffInDays($p->fecha_necesaria, false) : null;
                    $urgente = $dias !== null && $dias <= 2 && in_array($p->estado->value, ['PENDIENTE', 'EN_PROCESO']);
                    $material = $p->peticionProductos->map(fn ($l) => $l->cantidad . ' ' . mb_strtolower($l->articulo?->nombre ?? $l->producto))->implode(', ');
                    $porServir = in_array($p->estado->value, ['PENDIENTE', 'EN_PROCESO']);
                @endphp
                <tr class="hover:bg-stone-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('pedidosLogistica.visualizar', $p->id) }}" class="font-semibold text-marca-950 hover:underline">{{ $nombreCurso($p->curso) }}</a>
                        <p class="text-xs text-slate-500">Petición #{{ $p->id }}{{ $p->almacen ? ' · sale de ' . $p->almacen->nombre : '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $p->centro?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($p->fecha_necesaria)
                            <span class="{{ $urgente ? 'font-bold text-amber-700' : '' }}">{{ $p->fecha_necesaria->format('d/m/Y') }}</span>
                            @if ($porServir)
                                <p class="text-xs text-slate-500">{{ $dias < 0 ? '¡atrasada!' : ($dias == 0 ? 'hoy' : 'en ' . (int) $dias . ' días') }}</p>
                            @endif
                        @else
                            <span class="text-slate-400">Sin fecha</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $material ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-interfaz.panel.etiqueta :color="$colorEstado[$p->estado->value] ?? 'gris'">{{ $textoEstado[$p->estado->value] ?? $p->estado->value }}</x-interfaz.panel.etiqueta>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('pedidosLogistica.visualizar', $p->id) }}"
                            class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold {{ $porServir ? 'bg-marca-800 text-white hover:bg-marca-900' : 'border border-stone-300 bg-white text-marca-900 hover:bg-stone-50' }}">
                            {{ $porServir ? 'Servir' : ($p->estado->value === 'ENTREGADA' ? 'Registrar devolución' : 'Ver') }}
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-tabla.tabla>
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>
