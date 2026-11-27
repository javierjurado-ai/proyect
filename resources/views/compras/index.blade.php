@php
    $euros = fn ($n) => number_format((float) $n, 2, ',', '.') . ' €';
@endphp

<x-interfaz.layout.app titulo="Compras" :rol="$rol" :usuario="$nombre">

    <x-interfaz.panel.cabecera titulo="Compras"
        :subtitulo="$rol === 'LOGISTA' ? 'Facturas de proveedor que has registrado tú' : 'Facturas recibidas de proveedores'">
        <x-slot:acciones>
            <a href="{{ route('compra.create') }}" class="inline-flex items-center rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white hover:bg-marca-900">+ Registrar compra</a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Sin pagar" :valor="$resumen['sinPagar']" :tono="$resumen['sinPagar'] ? 'aviso' : 'ok'" />
        <x-interfaz.panel.metrica titulo="Vencen esta semana" :valor="$resumen['vencenSemana']" :tono="$resumen['vencenSemana'] ? 'alerta' : 'normal'" />
        <x-interfaz.panel.metrica titulo="Importe pendiente" :valor="$euros($resumen['pendiente'])" />
        <x-interfaz.panel.metrica titulo="Registradas este mes" :valor="$resumen['esteMes']" />
    </div>

    <x-interfaz.panel.tarjeta titulo="Facturas de compra">
        <nav aria-label="Filtrar por estado" class="mb-4 flex flex-wrap gap-1">
            @foreach ($pestanas as $clave => [$texto, $cuenta])
                <a href="{{ route('compra.index', ['estado' => $clave]) }}" @if ($filtro === $clave) aria-current="page" @endif
                    class="rounded-lg px-3 py-2 text-sm font-semibold {{ $filtro === $clave ? 'bg-marca-950 text-white' : 'text-slate-700 hover:bg-stone-100' }}">
                    {{ $texto }} <span class="opacity-70">{{ $cuenta }}</span>
                </a>
            @endforeach
        </nav>

        <x-tabla.tabla :columnas="['Factura', 'Proveedor', 'Fecha', 'Vence', ['Total', 'right'], 'Estado', ['Acciones', 'right']]" vacio="No hay compras en este estado.">
            @foreach ($compras as $f)
                @php
                    $pendiente = $f->estado->value === 'PENDIENTE';
                    $vencida = $pendiente && $f->fecha_vencimiento && $f->fecha_vencimiento->lt(today());
                @endphp
                <tr class="hover:bg-stone-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('compra.visualizar', $f->id) }}" class="font-semibold text-marca-900 hover:underline">{{ $f->numero_factura }}</a>
                        <p class="text-xs text-slate-500">{{ $f->lineas->pluck('concepto')->take(2)->implode(', ') }}{{ $f->lineas->count() > 2 ? '…' : '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $f->empresa?->nombre ?? ($f->docente ? trim($f->docente->nombre . ' ' . $f->docente->primer_apellido) : $f->proveedor_nombre) }}</td>
                    <td class="px-4 py-3">{{ $f->fecha->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 {{ $vencida ? 'font-bold text-red-700' : '' }}">{{ $f->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $euros($f->total) }}</td>
                    <td class="px-4 py-3">
                        <x-interfaz.panel.etiqueta :color="match ($f->estado->value) { 'PAGADA' => 'verde', 'ANULADA' => 'gris', default => $vencida ? 'rojo' : 'ambar' }">
                            {{ match ($f->estado->value) { 'PAGADA' => 'Pagada' . ($f->fecha_pago ? ' ' . $f->fecha_pago->format('d/m') : ''), 'ANULADA' => 'Anulada', default => $vencida ? 'Vencida' : 'Sin pagar' } }}
                        </x-interfaz.panel.etiqueta>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('compra.visualizar', $f->id) }}" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">Ver</a>
                            @if ($pendiente)
                                <form method="POST" action="{{ route('compra.pagar', $f->id) }}" onsubmit="return confirm('¿Marcar la factura {{ $f->numero_factura }} como pagada hoy?')">
                                    @csrf
                                    <input type="hidden" name="fecha_pago" value="{{ today()->toDateString() }}">
                                    <button type="submit" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">Marcar pagada</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-tabla.tabla>
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>