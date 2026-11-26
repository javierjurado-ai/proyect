{{-- Créditos formativos FUNDAE de las empresas cliente, por año --}}
@php
    $euros = fn ($n) => number_format((float) $n, 2, ',', '.') . ' €';
    $boton = 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold';
@endphp

<x-interfaz.layout.app titulo="Créditos" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Créditos formativos"
        subtitulo="Crédito FUNDAE de cada empresa en {{ $anio }}: lo asignado, lo gastado y lo que queda por aprovechar">
        <x-slot:acciones>
            {{-- Selector de año --}}
            <form method="GET" action="{{ route('credito.index') }}" class="flex items-center gap-2">
                <label for="anio" class="text-sm font-medium text-slate-600">Año</label>
                <select id="anio" name="anio" onchange="this.form.submit()"
                        class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm font-semibold text-marca-950 focus:outline-none focus:ring-2 focus:ring-marca-700">
                    @foreach ($anios as $a)
                        <option value="{{ $a }}" @selected($a === $anio)>{{ $a }}</option>
                    @endforeach
                </select>
                <noscript><button class="{{ $boton }} border border-stone-300 bg-white">Ver</button></noscript>
            </form>
            <a href="{{ route('credito.create', ['anio' => $anio]) }}" class="{{ $boton }} bg-marca-800 text-white hover:bg-marca-900">
                + Registrar crédito
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.avisos />

    {{-- Aviso de caducidad (solo para el año en curso) --}}
    @if ($diasParaCaducar !== null && $resumen['disponible'] > 0)
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm text-amber-900">
            <svg class="h-5 w-5 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <p>
                <strong>{{ $euros($resumen['disponible']) }}</strong> de crédito sin usar caducan en
                <strong>{{ $diasParaCaducar }} {{ $diasParaCaducar === 1 ? 'día' : 'días' }}</strong> (31/12/{{ $anio }}).
            </p>
        </div>
    @endif

    {{-- Indicadores --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Crédito asignado" :valor="$euros($resumen['total'])"
            :detalle="$filas->count() . ' ' . ($filas->count() === 1 ? 'empresa' : 'empresas') . ' con crédito'" />
        <x-interfaz.panel.metrica titulo="Consumido" :valor="$euros($resumen['consumido'])" />
        <x-interfaz.panel.metrica titulo="Disponible" :valor="$euros($resumen['disponible'])"
            :detalle="$diasParaCaducar !== null ? 'caduca el 31/12' : null" :tono="$diasParaCaducar !== null ? 'aviso' : 'normal'" />

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <p class="text-sm text-slate-600">Uso del crédito</p>
            <p class="mt-2 text-3xl font-bold text-marca-950">{{ $resumen['porcentaje'] }} %</p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-200" role="img" aria-label="{{ $resumen['porcentaje'] }} % consumido">
                <div class="h-full rounded-full bg-emerald-600" style="width: {{ min(100, $resumen['porcentaje']) }}%"></div>
            </div>
        </div>
    </div>

    {{-- Crédito por empresa --}}
    <x-interfaz.panel.tarjeta titulo="Crédito por empresa" class="mb-6">
        <p class="-mt-2 mb-4 text-sm text-slate-500">Ordenadas por crédito disponible: las de arriba son las que más pueden aprovechar.</p>

        <x-tabla.tabla vacio="No hay créditos registrados en {{ $anio }}."
            :columnas="['Empresa', ['Trab.', 'right'], ['Crédito', 'right'], ['Consumido', 'right'], ['Disponible', 'right'], 'Uso', 'Estado', ['Acciones', 'center']]">
            @foreach ($filas as $f)
                <x-tabla.fila>
                    <td class="px-4 py-3">
                        @if ($f->empresa)
                            <a href="{{ route('empresa.visualizar', $f->empresa) }}" class="font-semibold text-marca-900 hover:underline">{{ $f->empresa->nombre }}</a>
                            <p class="text-xs text-slate-500">CIF {{ $f->empresa->cif }}</p>
                        @else
                            <span class="text-slate-500">Empresa #{{ $f->credito->empresa_id }}</span>
                        @endif
                        @if ($f->aviso)
                            <p class="mt-1 text-xs font-medium text-amber-700">⚠ {{ $f->aviso }}</p>
                        @endif
                    </td>
                    <x-tabla.celda tipo="numero" :valor="$f->empresa?->total_trabajadores" />
                    <x-tabla.celda tipo="moneda" :valor="$f->total" />
                    <x-tabla.celda tipo="moneda" :valor="$f->consumido" />
                    <td class="whitespace-nowrap px-4 py-3 text-right font-semibold {{ $f->disponible > 0 ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $euros($f->disponible) }}
                    </td>
                    <td class="w-44 px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-stone-200" role="img" aria-label="{{ $f->porcentaje }} % consumido">
                                <div class="h-full rounded-full {{ $f->porcentaje >= 80 ? 'bg-amber-500' : 'bg-emerald-600' }}" style="width: {{ min(100, $f->porcentaje) }}%"></div>
                            </div>
                            <span class="w-10 text-right text-xs text-slate-600">{{ $f->porcentaje }} %</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <x-interfaz.panel.etiqueta :color="$f->estado[1]">{{ $f->estado[0] }}</x-interfaz.panel.etiqueta>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-flex items-center gap-2">
                            @if ($f->disponible > 0)
                                <a href="{{ route('presupuesto.create') }}"
                                   class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-medium text-marca-950 hover:bg-stone-50">
                                    Presupuesto
                                </a>
                            @endif
                            <x-tabla.boton-editar :href="route('credito.edit', $f->credito->id)" />
                            <x-tabla.boton-eliminar :action="route('credito.destroy', $f->credito->id)"
                                mensaje="¿Eliminar el crédito {{ $anio }} de esta empresa?" />
                        </div>
                    </td>
                </x-tabla.fila>
            @endforeach
        </x-tabla.tabla>
    </x-interfaz.panel.tarjeta>

    {{-- Clientes sin crédito ese año --}}
    <x-interfaz.panel.tarjeta titulo="Empresas sin crédito registrado en {{ $anio }}">
        @if ($empresasSinCredito->isEmpty())
            <p class="text-sm text-slate-500">Todas las empresas cliente tienen su crédito de {{ $anio }} registrado.</p>
        @else
            <ul class="divide-y divide-stone-200">
                @foreach ($empresasSinCredito as $e)
                    <li class="flex items-center justify-between gap-4 py-3">
                        <div class="min-w-0">
                            <a href="{{ route('empresa.visualizar', $e) }}" class="font-semibold text-marca-900 hover:underline">{{ $e->nombre }}</a>
                            <p class="text-xs text-slate-500">
                                CIF {{ $e->cif }}@if ($e->total_trabajadores) · {{ $e->total_trabajadores }} trab.@endif
                            </p>
                        </div>
                        <a href="{{ route('credito.create', ['empresa_id' => $e->id, 'anio' => $anio]) }}"
                           class="inline-flex shrink-0 items-center rounded-lg bg-marca-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-marca-900">
                            Registrar crédito
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>
