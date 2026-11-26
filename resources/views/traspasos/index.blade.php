@php
    $colorEstado = ['PENDIENTE' => 'ambar', 'EN_PROCESO' => 'azul', 'COMPLETADA' => 'verde', 'CANCELADA' => 'rojo'];
    $hoy = today();
@endphp

<x-interfaz.layout.app titulo="Traspasos entre almacenes" :rol="$rol" :usuario="$nombre" :contadores="['traspasos' => $porEnviar ?: null]">

    <x-interfaz.panel.cabecera titulo="Traspasos entre almacenes" subtitulo="Material que otros almacenes te piden y lo que tú les pides a ellos">
        <x-slot:acciones>
            <a href="{{ route('traspaso.create', ['destino' => $almacen?->id]) }}" class="inline-flex items-center rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white hover:bg-marca-900">+ Pedir a otro almacén</a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    @if (! $almacen)
        <p class="rounded-lg border border-dashed border-slate-300 py-10 text-center text-slate-500">No hay almacenes dados de alta.</p>
    @else
        @if ($misAlmacenes->count() > 1)
            <form method="GET" action="{{ route('traspaso.index') }}" class="mb-4 flex items-center gap-2 text-sm">
                <input type="hidden" name="lado" value="{{ $lado }}">
                <label for="almacen_id" class="text-slate-600">Ver como</label>
                <select id="almacen_id" name="almacen_id" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2">
                    @foreach ($misAlmacenes as $a)
                        <option value="{{ $a->id }}" @selected($a->id === $almacen->id)>{{ $a->nombre }}</option>
                    @endforeach
                </select>
            </form>
        @endif

        <nav aria-label="Dirección" class="mb-6 grid gap-3 sm:grid-cols-2">
            @foreach (['mepiden' => ['Me piden', $porEnviar ? $porEnviar . ' por enviar desde ' . $almacen->nombre : 'Nada por enviar'],
                       'pedido'  => ['He pedido', $enCamino ? $enCamino . ' pendientes de llegar a ' . $almacen->nombre : 'Nada pendiente de llegar']] as $clave => [$titulo, $sub])
                <a href="{{ route('traspaso.index', ['almacen_id' => $almacen->id, 'lado' => $clave]) }}" @if ($lado === $clave) aria-current="page" @endif
                    class="rounded-xl border-2 px-4 py-3 {{ $lado === $clave ? 'border-marca-950 bg-marca-950 text-white' : 'border-stone-200 bg-white text-slate-800 hover:border-stone-300' }}">
                    <span class="block font-bold">{{ $titulo }}</span>
                    <span class="mt-1 block text-sm opacity-85">{{ $sub }}</span>
                </a>
            @endforeach
        </nav>

        <x-interfaz.panel.tarjeta :titulo="$lado === 'pedido' ? 'Lo que has pedido a otros almacenes' : 'Lo que te piden otros almacenes'">
            <x-tabla.tabla :columnas="['Traspaso', $lado === 'pedido' ? 'Se lo pides a' : 'Lo pide', 'Material', 'Se necesita', 'Estado', ['Acción', 'right']]"
                :vacio="$lado === 'pedido' ? 'No has pedido nada a otros almacenes.' : 'Ningún almacén te ha pedido material.'">
                @foreach ($traspasos as $t)
                    @php
                        $abierto = in_array($t->estado->value, ['PENDIENTE', 'EN_PROCESO']);
                        $dias = $t->fecha_necesaria ? $hoy->diffInDays($t->fecha_necesaria, false) : null;
                        $material = $t->peticionProductos->map(fn ($l) => $l->cantidad . ' ' . mb_strtolower($l->articulo?->nombre ?? $l->producto))->implode(', ');
                        $curso = $t->curso?->formacion?->nombre;
                    @endphp
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('pedidosLogistica.visualizar', $t->id) }}" class="font-semibold text-marca-950 hover:underline">TR-{{ $t->id }}</a>
                            <p class="text-xs text-slate-500">{{ $curso ? 'Para el curso ' . $curso : ($t->observaciones ?: 'Reposición') }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $lado === 'pedido' ? $t->almacen?->nombre : $t->almacenDestino?->nombre }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $material }}</td>
                        <td class="px-4 py-3">
                            <span class="{{ $abierto && $dias !== null && $dias <= 2 ? 'font-bold text-amber-700' : '' }}">{{ $t->fecha_necesaria?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-interfaz.panel.etiqueta :color="$colorEstado[$t->estado->value] ?? 'gris'">
                                {{ match ($t->estado->value) { 'PENDIENTE' => $lado === 'pedido' ? 'Esperando envío' : 'Pendiente de enviar', 'EN_PROCESO' => 'Enviado en parte', 'COMPLETADA' => 'Recibido', default => ucfirst(strtolower($t->estado->value)) } }}
                            </x-interfaz.panel.etiqueta>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('pedidosLogistica.visualizar', $t->id) }}"
                                class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold {{ $abierto && $lado === 'mepiden' ? 'bg-marca-800 text-white hover:bg-marca-900' : 'border border-stone-300 bg-white text-marca-900 hover:bg-stone-50' }}">
                                {{ $abierto && $lado === 'mepiden' ? 'Enviar' : 'Ver' }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </x-tabla.tabla>
        </x-interfaz.panel.tarjeta>

    @endif

</x-interfaz.layout.app>
