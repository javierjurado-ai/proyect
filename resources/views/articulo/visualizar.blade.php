@php
    $texto = fn ($enum) => $enum ? ucfirst(strtolower(str_replace('_', ' ', $enum->value))) : '—';
    $euros = fn ($n) => $n !== null ? number_format((float) $n, 2, ',', '.') . ' €' : '—';
    $colorEstado = ['DISPONIBLE' => 'verde', 'PRESTADO' => 'ambar', 'EN_REPARACION' => 'azul', 'BAJA' => 'gris'];
    $total = (int) $stockPorAlmacen->sum('cantidad');
@endphp

<x-interfaz.layout.app :titulo="$articulo->nombre" :rol="$rol" :usuario="$nombre">

    <x-interfaz.panel.cabecera :titulo="$articulo->nombre" :subtitulo="$articulo->codigo . ' · ' . $texto($articulo->tipo)">
        <x-slot:acciones>
            <a href="{{ route('articulo.index') }}" class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">← Volver a artículos</a>
            <a href="{{ route('articulo.edit', $articulo->id) }}" class="inline-flex items-center rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white hover:bg-marca-900">Editar</a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    <div class="grid gap-6 lg:grid-cols-2">
        <x-interfaz.panel.tarjeta titulo="Datos">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-500">Código</dt><dd class="font-mono">{{ $articulo->codigo }}</dd></div>
                <div><dt class="text-slate-500">Estado</dt><dd>
                    <x-interfaz.panel.etiqueta :color="$articulo->activo ? 'verde' : 'gris'">{{ $articulo->activo ? 'Activo' : 'Inactivo' }}</x-interfaz.panel.etiqueta>
                </dd></div>
                <div><dt class="text-slate-500">Tipo</dt><dd>{{ $texto($articulo->tipo) }}</dd></div>
                <div><dt class="text-slate-500">Se cuenta por</dt><dd>{{ $texto($articulo->unidad) }}</dd></div>
                <div><dt class="text-slate-500">Retornable</dt><dd>{{ $articulo->retornable?->value === 'SI' ? 'Sí, vuelve al acabar el curso' : 'No, se gasta' }}</dd></div>
                <div><dt class="text-slate-500">Nº de serie</dt><dd>{{ $articulo->controla_serie?->value === 'SI' ? 'Sí, unidad a unidad' : 'No' }}</dd></div>
                <div><dt class="text-slate-500">Precio de coste</dt><dd>{{ $euros($articulo->precio_coste) }}</dd></div>
                <div><dt class="text-slate-500">Precio de venta</dt><dd>{{ $euros($articulo->precio_venta) }}</dd></div>
                <div><dt class="text-slate-500">IVA</dt><dd>{{ rtrim(rtrim(number_format((float) $articulo->tipo_iva, 2, ',', ''), '0'), ',') }} %</dd></div>
                <div><dt class="text-slate-500">Stock mínimo</dt><dd>{{ $articulo->stock_minimo ?: '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-slate-500">Proveedor habitual</dt><dd>{{ $articulo->proveedorHabitual?->nombre ?? '—' }}</dd></div>
                @if ($articulo->descripcion)
                    <div class="col-span-2"><dt class="text-slate-500">Descripción</dt><dd class="whitespace-pre-line">{{ $articulo->descripcion }}</dd></div>
                @endif
            </dl>
        </x-interfaz.panel.tarjeta>

        <x-interfaz.panel.tarjeta titulo="Stock por almacén">
            <x-tabla.tabla :columnas="['Almacén', ['Cantidad', 'right']]" vacio="No hay stock de este artículo en ningún almacén.">
                @foreach ($stockPorAlmacen as $s)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('almacen.index', ['almacen_id' => $s->almacen_id]) }}" class="text-marca-900 hover:underline">{{ $s->almacen?->nombre }}</a>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $s->cantidad }}</td>
                    </tr>
                @endforeach
            </x-tabla.tabla>
            <p class="mt-3 text-sm {{ $articulo->stock_minimo > 0 && $total < $articulo->stock_minimo ? 'font-semibold text-red-700' : 'text-slate-600' }}">
                Total: {{ $total }}
                @if ($articulo->stock_minimo > 0 && $total < $articulo->stock_minimo)
                    · por debajo del mínimo ({{ $articulo->stock_minimo }}), hay que reponer
                @endif
            </p>
        </x-interfaz.panel.tarjeta>
    </div>

    @if ($articulo->controla_serie?->value === 'SI')
        <x-interfaz.panel.tarjeta titulo="Unidades con nº de serie" class="mt-6">
            <x-tabla.tabla :columnas="['Nº de serie', 'Estado', 'Dónde está', 'Alta']" vacio="Todavía no hay unidades. Entran al registrar una compra.">
                @foreach ($unidades as $u)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3 font-mono">{{ $u->numero_serie }}</td>
                        <td class="px-4 py-3">
                            <x-interfaz.panel.etiqueta :color="$colorEstado[$u->estado->value] ?? 'gris'">{{ $texto($u->estado) }}</x-interfaz.panel.etiqueta>
                        </td>
                        <td class="px-4 py-3">
                            @if ($u->estado->value === 'PRESTADO' && $u->curso)
                                Curso {{ $u->curso->codigo_accion }}/{{ $u->curso->codigo_grupo }} · {{ $u->curso->formacion?->nombre }}
                            @else
                                {{ $u->almacen?->nombre ?? '—' }}
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $u->fecha_alta ? \Illuminate\Support\Carbon::parse($u->fecha_alta)->format('d/m/Y') : '—' }}</td>
                    </tr>
                @endforeach
            </x-tabla.tabla>
        </x-interfaz.panel.tarjeta>
    @endif

</x-interfaz.layout.app>
