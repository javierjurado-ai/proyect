@php
    // Enlace a una ruta solo si existe (algunas secciones aún no tienen página)
    $ruta = fn (string $nombre) => \Illuminate\Support\Facades\Route::has($nombre) ? route($nombre) : null; // null = página aún sin hacer
    $euros = fn ($n) => number_format((float) $n, 0, ',', '.') . ' €';

    $areas = [
        [
            'titulo' => 'Formación',
            'franja' => 'bg-marca-800',
            'items'  => [
                ['Cursos',      $totalCursos,      'curso.crearTablaCurso', null],
                ['Formaciones', $totalFormaciones, 'formacion.index',       null],
                ['Aulas',       $totalAulas,       'aula.index',            null],
                ['Centros',     $totalCentros,     'centro.index',          null],
                ['Alumnos',     $totalAlumnos,     'alumno.index',          null],
                ['Docentes',    $totalDocentes,    'docente.index',         null],
            ],
        ],
        [
            'titulo' => 'Gestión',
            'franja' => 'bg-amber-400',
            'items'  => [
                ['Comunicaciones FUNDAE', $fundaePendientes, 'fundae.index',
                    $fundaeVencidas ? [$fundaeVencidas . ' vencida' . ($fundaeVencidas > 1 ? 's' : ''), 'text-red-700'] : ($fundaePendientes ? ['pendientes', 'text-slate-500'] : null)],
                ['Logística', $logisticaPendiente, 'logistica.index',
                    $logisticaPendiente ? ['pendientes', 'text-amber-700'] : null],
                ['Empresas',     $totalEmpresas,     'empresa.index',     null],
                ['Presupuestos', $totalPresupuestos, 'presupuesto.index', null],
            ],
        ],
        [
            'titulo' => 'Administración',
            'franja' => 'bg-violet-500',
            'items'  => [
                ['Usuarios', $totalUsuarios, 'usuario.index',
                    $usuariosSinRol ? [$usuariosSinRol . ' sin rol', 'text-red-700'] : null],
                ['Personal con rol', $totalPersonal, 'usuario.index', null],
                ['Créditos ' . $anio, $totalCreditos, 'credito.index',
                    $empresasSinCredito->isNotEmpty() ? [$empresasSinCredito->count() . ' empresas sin crédito', 'text-amber-700'] : null],
            ],
        ],
    ];

    // Lista de cosas que revisar (solo aparecen las que hay)
    $revisar = [];

    if ($usuariosSinRol) {
        $revisar[] = ['Accesos', 'rojo', $usuariosSinRol . ' usuario' . ($usuariosSinRol > 1 ? 's' : '') . ' sin rol',
            'No puede' . ($usuariosSinRol > 1 ? 'n' : '') . ' entrar: le sale «No tienes acceso»', 'Asignar rol', $ruta('usuario.index')];
    }
    if ($personasSinUsuario) {
        $revisar[] = ['Accesos', 'ambar', $personasSinUsuario . ' persona' . ($personasSinUsuario > 1 ? 's' : '') . ' con rol y sin usuario',
            'Tienen rol de coordinador, comercial o administrador, pero no cuenta para entrar', 'Crear usuario', $ruta('usuario.index')];
    }
    if ($fundaeVencidas) {
        $revisar[] = ['FUNDAE', 'rojo', $fundaeVencidas . ' comunicaci' . ($fundaeVencidas > 1 ? 'ones vencidas' : 'ón vencida'),
            'La fecha límite ya ha pasado y no se ha enviado', 'Ver', $ruta('fundae.index')];
    }
    if ($empresasSinCredito->isNotEmpty()) {
        $revisar[] = ['Créditos', 'ambar', $empresasSinCredito->count() . ' empresa' . ($empresasSinCredito->count() > 1 ? 's' : '') . ' sin crédito ' . $anio,
            $empresasSinCredito->take(2)->implode(', ') . ($empresasSinCredito->count() > 2 ? ' y ' . ($empresasSinCredito->count() - 2) . ' más' : ''),
            'Registrar', $ruta('credito.index')];
    }
@endphp


<x-interfaz.layout.app titulo="Inicio" :rol="$rol" :usuario="$nombre" :contadores="['fundae' => $fundaePendientes ?: null, 'logistica' => $logisticaPendiente ?: null, 'usuarios' => $usuariosSinRol ?: null]">
        <x-interfaz.panel.cabecera :titulo="'Hola, ' . $primerNombre" subtitulo="Resumen de toda la plataforma">
        <x-slot:acciones>
            @if ($ruta('usuario.index'))
                <a href="{{ $ruta('usuario.index') }}"
                   class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    + Nuevo usuario
                </a>
            @endif
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>
    {{-- Indicadores--}}
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Usuarios" :valor="$totalUsuarios"
            :detalle="$usuariosSinRol ? $usuariosSinRol . ' sin rol' : 'todos con rol'"
            :tono="$usuariosSinRol ? 'alerta' : 'ok'" />

        <x-interfaz.panel.metrica titulo="Cursos en marcha" :valor="$cursosEnMarcha"
            :detalle="$cursosEstaSemana . ' empiezan esta semana'" />

        <x-interfaz.panel.metrica titulo="Empresas" :valor="$totalEmpresas" />

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <p class="text-sm text-slate-600">Crédito consumido {{ $anio }}</p>
            <p class="mt-2 text-3xl font-bold text-marca-950">{{ $porcentajeCredito }} %</p>
            <p class="mt-2 text-xs text-slate-600">{{ $euros($creditoConsumido) }} de {{ $euros($creditoTotal) }}</p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-200" role="img" aria-label="{{ $porcentajeCredito }} % consumido">
                <div class="h-full rounded-full bg-emerald-600" style="width: {{ min(100, $porcentajeCredito) }}%"></div>
            </div>
        </div>
    </div>
    {{-- Tarjetas por área --}}
        <div class="mb-6 grid gap-6 lg:grid-cols-3">
        @foreach ($areas as $area)
            <section class="overflow-hidden rounded-xl border border-stone-200 bg-white">
                <div class="h-1.5 {{ $area['franja'] }}"></div>
                <div class="p-5">
                    <h2 class="mb-4 text-lg font-bold text-marca-950">{{ $area['titulo'] }}</h2>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($area['items'] as [$texto, $valor, $nombreRuta, $nota])
                            @php $url = $ruta($nombreRuta); @endphp
                            {{-- Si la página aún no existe, la tarjeta se muestra sin enlace --}}
                            <{{ $url ? 'a' : 'div' }} @if ($url) href="{{ $url }}" @endif
                               class="flex flex-col gap-0.5 rounded-lg bg-stone-50 p-3 {{ $url ? 'transition hover:bg-marca-50' : '' }}">
                                <span class="text-2xl font-bold text-marca-950">{{ $valor }}</span>
                                <span class="text-sm font-semibold text-slate-600">{{ $texto }}</span>
                                @if ($nota)
                                    <span class="text-xs font-semibold {{ $nota[1] }}">{{ $nota[0] }}</span>
                                @endif
                            </{{ $url ? 'a' : 'div' }}>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach
    </div>
     <div class="grid gap-6 lg:grid-cols-5">

        {{-- Revisar --}}
        <x-interfaz.panel.tarjeta titulo="Revisar" class="lg:col-span-3">
            @if (empty($revisar))
                <p class="text-sm text-slate-500">Todo en orden. No hay nada que revisar.</p>
            @else
                <ul class="flex flex-col gap-2">
                    @foreach ($revisar as [$etiqueta, $color, $titulo, $detalle, $accion, $href])
                        <x-interfaz.panel.tarea :etiqueta="$etiqueta" :color="$color" :titulo="$titulo"
                            :detalle="$detalle" :accion="$accion" :href="$href" />
                    @endforeach
                </ul>
            @endif
        </x-interfaz.panel.tarjeta>

    {{-- Crédito por empresa --}}
        <x-interfaz.panel.tarjeta titulo="Crédito por empresa" :enlace="$ruta('credito.index')" texto-enlace="Ver créditos" class="lg:col-span-2">
            @if ($creditoPorEmpresa->isEmpty())
                <p class="text-sm text-slate-500">No hay créditos registrados para {{ $anio }}.</p>
            @else
                <ul class="divide-y divide-stone-100">
                    @foreach ($creditoPorEmpresa as $c)
                        <li class="flex flex-col gap-1.5 py-3">
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="font-semibold text-slate-900">{{ $c['empresa'] }}</span>
                                <span class="whitespace-nowrap text-slate-600">{{ $euros($c['consumido']) }} de {{ $euros($c['total']) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-stone-200" role="img" aria-label="{{ $c['porcentaje'] }} % consumido">
                                <div class="h-full rounded-full {{ $c['porcentaje'] >= 80 ? 'bg-amber-500' : 'bg-emerald-600' }}"
                                     style="width: {{ min(100, $c['porcentaje']) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-interfaz.panel.tarjeta>
    </div>

</x-interfaz.layout.app> 