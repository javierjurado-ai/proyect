{{-- Detalle de una compra (factura de proveedor) --}}
@php
    $euros = fn ($n) => number_format((float) $n, 2, ',', '.') . ' €';
    $num = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');

    $estado    = $compra->estado->value;
    $pendiente = $estado === 'PENDIENTE';
    $vencida   = $pendiente && $compra->fecha_vencimiento && $compra->fecha_vencimiento->lt(today());

    [$textoEstado, $colorEstado] = match ($estado) {
        'PAGADA'  => ['Pagada' . ($compra->fecha_pago ? ' el ' . $compra->fecha_pago->format('d/m/Y') : ''), 'verde'],
        'ANULADA' => ['Anulada', 'gris'],
        default   => [$vencida ? 'Vencida' : 'Sin pagar', $vencida ? 'rojo' : 'ambar'],
    };

    $formasPago = ['TRANSFERENCIA' => 'Transferencia', 'DOMICILIACION' => 'Domiciliación', 'TARJETA' => 'Tarjeta', 'EFECTIVO' => 'Efectivo'];
    $nombreCurso = fn ($c) => $c ? ($c->formacion?->nombre ?? 'Curso ' . $c->codigo_accion . '/' . $c->codigo_grupo) : null;
    $registradaPor = $compra->persona ? trim($compra->persona->nombre . ' ' . $compra->persona->primer_apellido) : '—';
@endphp

<x-interfaz.layout.app :titulo="'Compra ' . $compra->numero_factura" :rol="$rol" :usuario="$nombre">

    <a href="{{ route('compra.index') }}" class="mb-4 inline-block text-sm font-semibold text-marca-900 hover:underline">← Compras</a>

    <x-interfaz.panel.cabecera :titulo="'Factura ' . $compra->numero_factura"
        :subtitulo="($compra->nombre_emisor ?? 'Proveedor') . ' · registrada por ' . $registradaPor . ' el ' . $compra->fecha_registro->format('d/m/Y')">
        <x-slot:acciones>
            <x-interfaz.panel.etiqueta :color="$colorEstado" class="px-3 py-1 text-sm">{{ $textoEstado }}</x-interfaz.panel.etiqueta>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    {{-- Datos principales --}}
    <dl class="mb-6 grid gap-px overflow-hidden rounded-xl border border-stone-200 bg-stone-200 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white px-4 py-3">
            <dt class="text-xs text-slate-500">Proveedor</dt>
            <dd class="mt-1 text-sm font-semibold">{{ $compra->nombre_emisor ?? '—' }}</dd>
            @if ($compra->empresa?->cif)
                <dd class="text-xs text-slate-500">CIF {{ $compra->empresa->cif }}</dd>
            @endif
        </div>
        <div class="bg-white px-4 py-3">
            <dt class="text-xs text-slate-500">Fecha de la factura</dt>
            <dd class="mt-1 text-sm font-semibold">{{ $compra->fecha->format('d/m/Y') }}</dd>
        </div>
        <div class="bg-white px-4 py-3">
            <dt class="text-xs text-slate-500">Vence</dt>
            <dd class="mt-1 text-sm font-semibold {{ $vencida ? 'text-red-700' : '' }}">{{ $compra->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</dd>
        </div>
        <div class="bg-white px-4 py-3">
            <dt class="text-xs text-slate-500">Forma de pago</dt>
            <dd class="mt-1 text-sm font-semibold">{{ $formasPago[$compra->forma_pago?->value] ?? '—' }}</dd>
        </div>
        <div class="bg-white px-4 py-3 sm:col-span-2">
            <dt class="text-xs text-slate-500">Imputada al curso</dt>
            <dd class="mt-1 text-sm font-semibold">{{ $nombreCurso($compra->curso) ?? 'Material general' }}</dd>
        </div>
        <div class="bg-white px-4 py-3 sm:col-span-2">
            <dt class="text-xs text-slate-500">Total</dt>
            <dd class="mt-1 text-lg font-bold text-marca-950">{{ $euros($compra->total) }}</dd>
        </div>
    </dl>

    @if ($compra->observaciones)
        <p class="mb-6 rounded-lg bg-stone-200/60 px-4 py-3 text-sm text-slate-700"><strong>Observaciones:</strong> {{ $compra->observaciones }}</p>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">

        <div class="flex flex-col gap-6 lg:col-span-2">

            {{-- Líneas --}}
            <x-interfaz.panel.tarjeta titulo="Líneas de la factura">
                <x-tabla.tabla vacio="La factura no tiene líneas."
                    :columnas="['Concepto', ['Cantidad', 'right'], ['Precio', 'right'], ['Dto.', 'right'], ['IVA', 'right'], ['Base', 'right'], ['Cuota IVA', 'right']]">
                    @foreach ($compra->lineas as $l)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3">
                                <span class="font-semibold">{{ $l->concepto }}</span>
                                @if ($l->articulo || $l->curso)
                                    <p class="text-xs text-slate-500">
                                        {{ $l->articulo?->codigo }}{{ $l->articulo && $l->curso ? ' · ' : '' }}{{ $nombreCurso($l->curso) }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">{{ $num($l->cantidad) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ $euros($l->precio_unitario) }}</td>
                            <td class="px-4 py-3 text-right">{{ (float) $l->descuento_pct > 0 ? $num($l->descuento_pct) . ' %' : '—' }}</td>
                            <td class="px-4 py-3 text-right">{{ ($l->exento?->value ?? $l->exento) === 'SI' ? 'Exento' : $num($l->tipo_iva) . ' %' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold">{{ $euros($l->base) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ $euros($l->cuota_iva) }}</td>
                        </tr>
                    @endforeach
                </x-tabla.tabla>

                {{-- Totales --}}
                <dl class="ml-auto mt-4 w-full max-w-xs space-y-1 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-600">Base imponible</dt><dd class="font-semibold">{{ $euros($compra->base_imponible) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-600">IVA</dt><dd class="font-semibold">{{ $euros($compra->total_iva) }}</dd></div>
                    @if ((float) $compra->retencion_irpf > 0)
                        <div class="flex justify-between"><dt class="text-slate-600">Retención IRPF</dt><dd class="font-semibold">− {{ $euros($compra->retencion_irpf) }}</dd></div>
                    @endif
                    <div class="flex justify-between border-t border-stone-200 pt-2 text-base"><dt class="font-bold">Total</dt><dd class="font-bold text-marca-950">{{ $euros($compra->total) }}</dd></div>
                </dl>
            </x-interfaz.panel.tarjeta>

            {{-- Entradas en almacén --}}
            <x-interfaz.panel.tarjeta titulo="Entrada en almacén">
                @forelse ($compra->movimientos as $m)
                    <div class="{{ $loop->first ? '' : 'mt-4 border-t border-stone-200 pt-4' }}">
                        <p class="mb-2 text-sm text-slate-600">
                            <strong class="text-slate-900">{{ $m->almacenDestino?->nombre ?? 'Almacén' }}</strong>
                            · {{ $m->fecha?->format('d/m/Y H:i') }}
                            @if ($m->persona) · {{ trim($m->persona->nombre . ' ' . $m->persona->primer_apellido) }} @endif
                        </p>
                        <ul class="divide-y divide-stone-100 rounded-lg border border-stone-200 text-sm">
                            @foreach ($m->lineas->groupBy('articulo_id') as $lineasArticulo)
                                @php $art = $lineasArticulo->first()->articulo; @endphp
                                <li class="flex items-start justify-between gap-4 px-3 py-2">
                                    <div>
                                        <span class="font-semibold">{{ $art?->nombre ?? 'Artículo' }}</span>
                                        @php $series = $lineasArticulo->pluck('unidad.numero_serie')->filter(); @endphp
                                        @if ($series->isNotEmpty())
                                            <p class="font-mono text-xs text-slate-500">{{ $series->implode(', ') }}</p>
                                        @endif
                                    </div>
                                    <span class="whitespace-nowrap font-semibold">{{ $lineasArticulo->sum('cantidad') }} ud.</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Esta compra no generó entrada de material en ningún almacén.</p>
                @endforelse
            </x-interfaz.panel.tarjeta>
        </div>

        {{-- Acciones --}}
        <div class="flex flex-col gap-6">
            @if ($pendiente)
                <x-interfaz.panel.tarjeta titulo="Pago">
                    <form method="POST" action="{{ route('compra.pagar', $compra->id) }}" class="flex flex-col gap-3"
                          onsubmit="return confirm('¿Marcar la factura {{ $compra->numero_factura }} como pagada?')">
                        @csrf
                        <x-formulario.entrada name="fecha_pago" type="date" label="Fecha de pago" :value="today()->toDateString()" />
                        <button type="submit" class="rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                            Marcar como pagada
                        </button>
                    </form>
                </x-interfaz.panel.tarjeta>
            @endif

            @if ($estado !== 'ANULADA')
                <x-interfaz.panel.tarjeta titulo="Anular factura">
                    <p class="mb-3 text-sm text-slate-600">
                        Para facturas registradas por error. No se borra: queda como anulada con el motivo.
                        @if ($compra->movimientos->isNotEmpty())
                            <strong>El material ya entró en el almacén:</strong> si no llegó, regístralo después con un ajuste.
                        @endif
                    </p>
                    <form method="POST" action="{{ route('compra.anular', $compra->id) }}" class="flex flex-col gap-3"
                          onsubmit="return confirm('¿Anular la factura {{ $compra->numero_factura }}?')">
                        @csrf
                        <label for="observaciones" class="text-sm font-medium text-slate-700">Motivo *</label>
                        <textarea id="observaciones" name="observaciones" rows="3" maxlength="300" required
                                  class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-marca-700">{{ old('observaciones') }}</textarea>
                        <button type="submit" class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                            Anular factura
                        </button>
                    </form>
                </x-interfaz.panel.tarjeta>
            @endif
        </div>
    </div>

</x-interfaz.layout.app>