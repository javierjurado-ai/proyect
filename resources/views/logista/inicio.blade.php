@php
    $ruta = fn (string $nombre) => \Illuminate\Support\Facades\Route::has($nombre) ? route($nombre) : null; // null = página aún sin hacer
@endphp

<x-interfaz.layout.app titulo="Inicio" :rol="$rol" :usuario="$nombre" :contadores="['pedidos' => $porServir ?: null, 'traspasos' => $traspasosPorEnviar ?: null]">

    <x-interfaz.panel.cabecera :titulo="'Hola, ' . $primerNombre"
        :subtitulo="($porServir || $traspasosPorEnviar)
            ? 'Tienes ' . $porServir . ($porServir === 1 ? ' petición de curso' : ' peticiones de cursos') . ' y ' . $traspasosPorEnviar . ($traspasosPorEnviar === 1 ? ' traspaso' : ' traspasos') . ' por enviar'
            : 'No hay nada por servir. ¡Todo al día!'">
        <x-slot:acciones>
            @if ($ruta('compra.create'))
                <a href="{{ $ruta('compra.create') }}" class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">
                    + Registrar compra
                </a>
            @endif
            @if ($ruta('pedidosLogistica.index'))
                <a href="{{ $ruta('pedidosLogistica.index') }}" class="inline-flex items-center rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    Ver peticiones
                </a>
            @endif
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if ($errors->has('permiso'))
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first('permiso') }}
        </div>
    @endif

    {{-- Indicadores --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Por servir" :valor="$porServir"
            :detalle="$urgentes ? $urgentes . ' se necesitan en 2 días o menos' : 'ninguna urgente'"
            :tono="$urgentes ? 'alerta' : 'ok'" />

        <x-interfaz.panel.metrica titulo="Material fuera" :valor="$materialFuera"
            :detalle="$devolucionesAtrasadas ? $devolucionesAtrasadas . ' devoluciones atrasadas' : 'unidades prestadas a cursos'"
            :tono="$devolucionesAtrasadas ? 'aviso' : 'normal'" />

        <x-interfaz.panel.metrica titulo="Bajo mínimo" :valor="$bajoMinimo->count()"
            :detalle="$bajoMinimo->count() ? $bajoMinimo->pluck('nombre')->take(2)->implode(', ') : 'stock correcto'"
            :tono="$bajoMinimo->count() ? 'alerta' : 'ok'" />

        <x-interfaz.panel.metrica titulo="Traspasos por enviar" :valor="$traspasosPorEnviar"
            detalle="material que te piden otros almacenes" :tono="$traspasosPorEnviar ? 'aviso' : 'normal'" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Peticiones por servir --}}
        <x-interfaz.panel.tarjeta titulo="Peticiones por servir" class="lg:col-span-2" :enlace="$ruta('pedidosLogistica.index')" texto-enlace="Ver todas">
            @if (empty($peticiones))
                <p class="text-sm text-slate-500">No hay peticiones pendientes.</p>
            @else
                <ul class="flex flex-col gap-2">
                    @foreach ($peticiones as $p)
                        <x-interfaz.panel.tarea :etiqueta="$p['etiqueta']" :color="$p['color']" :titulo="$p['titulo']"
                            :detalle="$p['detalle']" accion="Servir" :href="route('pedidosLogistica.visualizar', $p['id'])" />
                    @endforeach
                </ul>
            @endif
        </x-interfaz.panel.tarjeta>

        {{-- Bajo mínimo --}}
        <x-interfaz.panel.tarjeta titulo="Stock bajo mínimo">
            @if ($bajoMinimo->isEmpty())
                <p class="text-sm text-slate-500">Ningún artículo por debajo del mínimo.</p>
            @else
                <ul class="divide-y divide-stone-200">
                    @foreach ($bajoMinimo as $a)
                        <x-interfaz.panel.elemento
                            :nombre="$a->nombre"
                            :detalle="$a->codigo . ' · mínimo ' . $a->stock_minimo"
                            :etiqueta="(int) $a->stock_total . ' en stock'"
                            color="rojo" />
                    @endforeach
                </ul>
            @endif
        </x-interfaz.panel.tarjeta>
    </div>

</x-interfaz.layout.app>
