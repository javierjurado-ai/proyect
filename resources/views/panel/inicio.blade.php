@php
    // Datos de ejemplo: sustitúyelos por los reales desde el controlador
    $usuario = auth()->user()->usuario ?? 'Usuario';
    $rol = 'COORDINADOR'; // prueba con 'ADMINISTRADOR' para ver el menú completo

    $tareas = [
        ['etiqueta' => 'Urgente',   'color' => 'rojo',  'titulo' => 'Comunicación FUNDAE sin enviar', 'detalle' => 'Fecha límite: mañana',  'accion' => 'Revisar', 'href' => '/comunicacionFundae/listar'],
        ['etiqueta' => 'Pendiente', 'color' => 'ambar', 'titulo' => 'Asignar aula a 2 cursos',        'detalle' => 'Cursos sin aula',       'accion' => 'Asignar', 'href' => route('curso.crearTablaCurso')],
        ['etiqueta' => 'Revisar',   'color' => 'azul',  'titulo' => 'Presupuesto pendiente de envío', 'detalle' => 'Empresa Ejemplo S.L.',  'accion' => 'Abrir',   'href' => route('presupuesto.index')],
    ];

    $cursos = [
        ['nombre' => 'Excel avanzado',        'detalle' => 'Empieza el 24/11/2026', 'etiqueta' => 'Mañana', 'color' => 'azul'],
        ['nombre' => 'Prevención de riesgos', 'detalle' => 'Empieza el 01/12/2026', 'etiqueta' => 'Tarde',  'color' => 'violeta'],
        ['nombre' => 'Inglés B1',             'detalle' => 'Empieza el 07/12/2026', 'etiqueta' => 'Mañana', 'color' => 'azul'],
    ];

    $altas = ['Ana López', 'Carlos Ruiz', 'María Pérez'];
@endphp

<x-interfaz.layout.app titulo="Inicio" :rol="$rol" :usuario="$usuario" :contadores="[]">

    <x-interfaz.panel.cabecera titulo="Inicio" subtitulo="Resumen de la actividad de hoy">
        <x-slot:acciones>
            <a href="{{ route('curso.crearFormulario') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                + Nuevo curso
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Métricas --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Cursos activos" valor="12" detalle="3 empiezan este mes" />
        <x-interfaz.panel.metrica titulo="Alumnos" valor="148" detalle="+8 esta semana" tono="ok" />
        <x-interfaz.panel.metrica titulo="Comunicaciones FUNDAE" valor="5" detalle="2 vencen pronto" tono="aviso" />
        <x-interfaz.panel.metrica titulo="Pendientes" valor="3" detalle="1 urgente" tono="alerta" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Tareas --}}
        <x-interfaz.panel.tarjeta titulo="Tareas pendientes" class="lg:col-span-2">
            <ul class="flex flex-col gap-2">
                @foreach ($tareas as $tarea)
                    <x-interfaz.panel.tarea
                        :etiqueta="$tarea['etiqueta']"
                        :color="$tarea['color']"
                        :titulo="$tarea['titulo']"
                        :detalle="$tarea['detalle']"
                        :accion="$tarea['accion']"
                        :href="$tarea['href']" />
                @endforeach
            </ul>
        </x-interfaz.panel.tarjeta>

        {{-- Últimas altas --}}
        <x-interfaz.panel.tarjeta titulo="Últimas altas" :enlace="route('persona.create')" texto-enlace="Nueva persona">
            <ul class="flex flex-col gap-3">
                @foreach ($altas as $nombre)
                    <li class="flex items-center gap-3">
                        <x-interfaz.panel.avatar :nombre="$nombre" />
                        <span class="text-sm font-semibold text-slate-900">{{ $nombre }}</span>
                    </li>
                @endforeach
            </ul>
        </x-interfaz.panel.tarjeta>

        {{-- Próximos cursos --}}
        <x-interfaz.panel.tarjeta titulo="Próximos cursos" :enlace="route('curso.crearTablaCurso')" class="lg:col-span-3">
            <ul class="divide-y divide-stone-200">
                @foreach ($cursos as $curso)
                    <x-interfaz.panel.elemento
                        :nombre="$curso['nombre']"
                        :detalle="$curso['detalle']"
                        :etiqueta="$curso['etiqueta']"
                        :color="$curso['color']" />
                @endforeach
            </ul>
        </x-interfaz.panel.tarjeta>

    </div>
</x-interfaz.layout.app>