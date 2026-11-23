@php
   $ruta = fn (string $nombre) => \Illuminate\Support\Facades\Route::has($nombre) ? route($nombre) : null; // null = página aún sin hacer

    $boton = 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold';  
@endphp

<x-interfaz.layout.app titulo="Inicio" :rol="$rol" :usuario="$nombre" :contadores="['fundae' => $fundaePendientes ?: null, 'logistica' => $logisticaPendiente ?: null]">

    <x-interfaz.panel.cabecera :titulo="'Hola, ' . $primerNombre"
        :subtitulo="count($tareas) ? 'Tienes ' . count($tareas) . (count($tareas) === 1 ? ' tarea pendiente' : ' tareas pendientes') : 'No tienes tareas pendientes. ¡Todo al día!'">
        <x-slot:acciones>
            @if ($ruta('logistica.index'))
                <a href="{{ $ruta('logistica.index') }}" class="{{ $boton }} border border-stone-300 bg-white text-marca-950 hover:bg-stone-50">
                    + Petición logística
                </a>
            @endif
            @if ($ruta('fundae.index'))
                <a href="{{ $ruta('fundae.index') }}" class="{{ $boton }} border border-stone-300 bg-white text-marca-950 hover:bg-stone-50">
                    + Comunicación FUNDAE
                </a>
            @endif
            <a href="{{ $ruta('curso.crearFormulario') }}" class="{{ $boton }} bg-marca-800 text-white hover:bg-marca-900">
                + Nuevo curso
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Indicadores --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Cursos en marcha" :valor="$cursosEnMarcha"
            :detalle="$cursosEstaSemana . ($cursosEstaSemana === 1 ? ' empieza' : ' empiezan') . ' esta semana'" />

        <x-interfaz.panel.metrica titulo="Comunicaciones FUNDAE" :valor="$fundaePendientes"
            :detalle="$fundaeUrgentes ? $fundaeUrgentes . ' vencen en 3 días o menos' : 'ninguna urgente'"
            :tono="$fundaeUrgentes ? 'alerta' : 'ok'" />

        <x-interfaz.panel.metrica titulo="Logística pendiente" :valor="$logisticaPendiente"
            detalle="peticiones sin completar" :tono="$logisticaPendiente ? 'aviso' : 'ok'" />

        <x-interfaz.panel.metrica titulo="Aulas libres" :valor="$aulasLibres . ' / ' . $aulas->count()"
            :detalle="$aulas->count() ? 'ocupación del ' . round(100 * ($aulas->count() - $aulasLibres) / $aulas->count()) . ' %' : 'sin aulas registradas'"
            tono="ok" />
    </div>
        <div class="mb-6 grid gap-6 lg:grid-cols-3">

        {{-- Requiere tu atención --}}
        <x-interfaz.panel.tarjeta titulo="Requiere tu atención" class="lg:col-span-2">
            @if (empty($tareas))
                <p class="text-sm text-slate-500">Nada pendiente por ahora.</p>
            @else
                <ul class="flex flex-col gap-2">
                    @foreach ($tareas as $t)
                        <x-interfaz.panel.tarea :etiqueta="$t['etiqueta']" :color="$t['color']" :titulo="$t['titulo']"
                            :detalle="$t['detalle']" :accion="$t['accion']" :href="$ruta($t['ruta'])" />
                    @endforeach
                </ul>
            @endif
        </x-interfaz.panel.tarjeta>
                {{-- Aulas --}}
        <x-interfaz.panel.tarjeta titulo="Aulas" :enlace="$ruta('aula.create')" texto-enlace="Nueva aula">
            @if ($aulas->isEmpty())
                <p class="text-sm text-slate-500">Todavía no hay aulas.</p>
            @else
                <ul class="divide-y divide-stone-200">
                    @foreach ($aulas as $aula)
                        @php $libre = $aula->estado === 'DISPONIBLE'; @endphp
                        <x-interfaz.panel.elemento
                            :nombre="$aula->nombre_aula"
                            :detalle="collect([$aula->uso_aula ? ucfirst(strtolower($aula->uso_aula)) : null, $aula->capacidad ? $aula->capacidad . ' plazas' : null])->filter()->implode(' · ')"
                            :etiqueta="$libre ? 'Libre' : 'Ocupada'"
                            :color="$libre ? 'verde' : 'gris'" />
                    @endforeach
                </ul>
            @endif
        </x-interfaz.panel.tarjeta>
    </div>
        {{-- Agenda de la semana --}}
    <x-interfaz.panel.tarjeta titulo="Esta semana" :enlace="$ruta('curso.crearTablaCurso')" texto-enlace="Ver cursos">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($semana as $dia)
                <div class="flex flex-col gap-2 rounded-lg p-2 {{ $dia['esHoy'] ? 'bg-amber-50 ring-1 ring-amber-200' : '' }}">
                    <p class="text-sm font-bold text-marca-950">
                        {{ $dia['titulo'] }}
                        @if ($dia['esHoy']) <span class="font-semibold text-amber-700">· hoy</span> @endif
                    </p>

                    @forelse ($dia['cursos'] as $c)
                        <div class="rounded-md border-l-4 px-3 py-2 {{ $c['tarde'] ? 'border-violet-500 bg-violet-50' : 'border-sky-600 bg-sky-50' }}">
                            <p class="text-xs font-semibold text-slate-600">{{ $c['hora'] }}</p>
                            <p class="text-sm font-semibold text-slate-900">{{ $c['nombre'] }}</p>
                            <p class="text-xs text-slate-500">{{ $c['lugar'] }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">Sin cursos</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>