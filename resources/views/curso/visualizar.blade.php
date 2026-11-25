<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
@php
    $formacion = $curso->formacion;
    $presupuesto = $curso->presupuesto;
    $peticionLogistica = $curso->peticionLogistica;
    $modalidad = $curso->modalidad->value ?? $curso->modalidad;

    $horaInicio = $curso->hora_inicio ? substr($curso->hora_inicio, 0, 5) : null;
    $horaFinal = $curso->hora_final ? substr($curso->hora_final, 0, 5) : null;
    $horario = $horaInicio ? $horaInicio . ($horaFinal ? ' – ' . $horaFinal : '') : 'Sin horario';

    // Eventos del calendario: un evento por tramo horario de cada día guardado
    // (cualquier día de la semana, también sábados y domingos)
    $eventosCalendario = $curso->calendario
        ->sortBy('fecha')
        ->flatMap(function ($dia) use ($curso) {
            $fecha = $dia->fecha->toDateString();
            $tramos = collect([[$dia->hora_inicio1, $dia->hora_fin1], [$dia->hora_inicio2, $dia->hora_fin2]])
                ->filter(fn($t) => $t[0] && $t[1]);

            // Datos que usa la barra de acciones al seleccionar el día
            $datosDia = [
                'dia' => $dia->id,
                'horario' => $tramos->map(fn($t) => substr($t[0], 0, 5) . ' – ' . substr($t[1], 0, 5))->implode(' y '),
                'editar' => route('curso.calendario.edit', [$curso, $dia]),
                'cancelar' => route('curso.calendario.destroy', [$curso, $dia]),
            ];

            return $tramos->map(fn($t) => [
                'title' => substr($t[0], 0, 5) . ' – ' . substr($t[1], 0, 5),
                'start' => $fecha . 'T' . substr($t[0], 0, 5),
                'end' => $fecha . 'T' . substr($t[1], 0, 5),
                'extendedProps' => $datosDia,
            ]);
        })
        ->values();

    // Periodo del curso como fondo, para ver de un vistazo qué días entran
    if ($curso->fecha_inicio && $curso->fecha_fin) {
        $eventosCalendario->push([
            'start' => \Illuminate\Support\Carbon::parse($curso->fecha_inicio)->toDateString(),
            'end' => \Illuminate\Support\Carbon::parse($curso->fecha_fin)->addDay()->toDateString(),
            'display' => 'background',
            'classNames' => ['periodo-curso'],
        ]);
    }

    $eventosCalendario = $eventosCalendario->all();
    $diasConSesion = $curso->calendario->count();
@endphp
<x-interfaz.layout.app titulo="Curso {{ $curso->codigo_accion }}/{{ $curso->codigo_grupo }}" :rol="$rol"
    :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="{{ $curso->formacion?->nombre ?? 'Curso' }}"
        subtitulo="Acción {{ $curso->codigo_accion }} · Grupo {{ $curso->codigo_grupo }}">
        <x-slot:acciones>
            <a href="{{ route('curso.crearTablaCurso') }}"
                class="inline-flex items-center rounded-lg border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-50">
                Volver
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.layout.pestanas :inicial="request('pestana') === 'calendario' ? 'calendario' : null">

        {{-- Curso --}}
        <x-slot:informacion title="Información">
            <x-interfaz.panel.tarjeta titulo="Curso">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Código de acción</dt>
                        <dd class="font-mono">{{ $curso->codigo_accion }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Código de grupo</dt>
                        <dd class="font-mono">{{ $curso->codigo_grupo }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Modalidad</dt>
                        <dd>{{ $modalidad === 'TELEFORMACION' ? 'Teleformación' : ($modalidad ? ucfirst(strtolower($modalidad)) : '—') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Horas</dt>
                        <dd>
                            Presenciales: {{ $curso->horas_presenciales ?? '—' }} ·
                            Teleformación: {{ $curso->horas_teleformacion ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Nivel de formación</dt>
                        <dd>{{ $curso->nivel_formacion ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Empresa formadora</dt>
                        <dd>{{ $curso->empresa_formadora ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Turno</dt>
                        <dd>{{ $curso->turno->value ?? ($curso->turno ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Horario</dt>
                        <dd>{{ $horaInicio ? $horario : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Inicio</dt>
                        <dd>{{ \Illuminate\Support\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Fin</dt>
                        <dd>{{ \Illuminate\Support\Carbon::parse($curso->fecha_fin)->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Aula</dt>
                        <dd>{{ $curso->aula ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Técnico comunidad</dt>
                        <dd>{{ $curso->tecnico_comunidad ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-slate-500">Objetivos</dt>
                        <dd class="whitespace-pre-line">{{ $curso->objetivos ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-slate-500">Contenido</dt>
                        <dd class="whitespace-pre-line">{{ $curso->contenido ?? '—' }}</dd>
                    </div>
                    @if ($curso->observaciones)
                        <div class="col-span-2">
                            <dt class="text-slate-500">Observaciones</dt>
                            <dd class="whitespace-pre-line">{{ $curso->observaciones }}</dd>
                        </div>
                    @endif
                </dl>
            </x-interfaz.panel.tarjeta>
            <x-interfaz.panel.tarjeta titulo="Formación">
                @if ($formacion)
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500">Nombre</dt>
                            <dd class="font-semibold">{{ $formacion->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Código de acción</dt>
                            <dd class="font-mono">{{ $formacion->codigo_accion ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Horas totales</dt>
                            <dd>{{ $formacion->horas_totales ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Estado</dt>
                            <dd>
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $formacion->activo ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $formacion->activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-slate-500">Este curso no tiene una formación asociada.</p>
                @endif
            </x-interfaz.panel.tarjeta>
            <x-interfaz.panel.tarjeta titulo="Presupuesto">
                @if ($presupuesto)
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500">Nombre</dt>
                            <dd class="font-semibold">{{ $presupuesto->nombre_formacion ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Número de alumnos</dt>
                            <dd class="font-mono">{{ $presupuesto->numero_alumnos ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Horas totales</dt>
                            <dd>{{ $presupuesto->horas_totales ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Ubicación de la formación</dt>
                            <dd>{{ $presupuesto->ubicacion_formacion ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Importe bonificado</dt>
                            <dd>{{ $presupuesto->importe_bonificado ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Fecha bonificada</dt>
                            <dd>{{ $presupuesto->fecha_bonificado?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-slate-500">Este curso no tiene presupuesto.</p>
                @endif
            </x-interfaz.panel.tarjeta>
            <x-interfaz.panel.tarjeta titulo="Logística">
                @if ($peticionLogistica)
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500">Fecha</dt>
                            <dd class="font-semibold">{{ $peticionLogistica->fecha?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Estado de la petición</dt>
                            <dd>{{ $peticionLogistica->estado ? ucfirst(strtolower(str_replace('_', ' ', $peticionLogistica->estado->value))) : '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Observaciones</dt>
                            <dd>{{ $peticionLogistica->observaciones ?? '—' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-slate-500">Este curso no tiene petición de logística.</p>
                @endif
            </x-interfaz.panel.tarjeta>
        </x-slot:informacion>
        <x-slot:alumnos title="Alumnos">
            <x-interfaz.panel.tarjeta titulo="Alumnos matriculados">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-slate-500">{{ $curso->alumnos->count() }} registrados</p>
                    <a href="{{ route('curso.alumno.store', $curso) }}"
                        class="inline-flex items-center rounded-lg bg-teal-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-900">
                        + Nuevo alumno
                    </a>
                </div>

                @if (session('ok'))
                    <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('ok') }}
                    </div>
                @endif

                <x-tabla.tabla vacio="Todavía no hay alumnos matriculados en este curso." :columnas="[
                    'Nombre',
                    'DNI / NIE',
                    'Email',
                    'Teléfono',
                    'Nacimiento',
                    'Nacionalidad',
                    ['LOPD', 'center'],
                    ['Acciones', 'center'],
                ]">

                    @foreach ($curso->alumnos as $cursoAlumno)
                        @php
                            $alumno = $cursoAlumno->persona;
                            $lopd = $alumno?->LOPD?->value ?? ($alumno?->LOPD ?? 'NO');
                        @endphp

                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $alumno ? $alumno->nombre . ' ' . $alumno->primer_apellido . ' ' . $alumno->segundo_apellido : '—' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-700">
                                {{ $alumno?->DNI ?? ($alumno?->NIE ?? '—') }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $alumno?->email ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $alumno?->telefono ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                {{ $alumno?->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $alumno?->nacionalidad ?? '—' }}</td>

                            <td class="px-4 py-3 text-center">
                                @if (strtoupper((string) $lopd) === 'SI')
                                    <span
                                        class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Sí</span>
                                @else
                                    <span
                                        class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">No</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">


                                    <x-tabla.boton-eliminar :action="route('curso.alumno.destroy', [$curso->id, $cursoAlumno->id])" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-tabla.tabla>
            </x-interfaz.panel.tarjeta>
        </x-slot:alumnos>
        <x-slot:docentes title="Docentes">
            <x-interfaz.panel.tarjeta titulo="Docentes trabajando">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-slate-500">{{ $curso->docentes->count() }} registrados</p>
                    <a href="{{ route('curso.docente.store', $curso) }}"
                        class="inline-flex items-center rounded-lg bg-teal-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-900">
                        + Nuevo docente
                    </a>
                </div>

                @if (session('ok'))
                    <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('ok') }}
                    </div>
                @endif

                <x-tabla.tabla vacio="Todavía no hay docentes en este curso." :columnas="[
                    'Nombre',
                    'DNI / NIE',
                    'Email',
                    'Teléfono',
                    'Nacimiento',
                    'Nacionalidad',
                    ['LOPD', 'center'],
                    ['Acciones', 'center'],
                ]">

                    @foreach ($curso->docentes as $cursoDocente)
                        @php
                            $docente = $cursoDocente->persona;
                            $lopd = $docente?->LOPD?->value ?? ($docente?->LOPD ?? 'NO');
                        @endphp

                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $docente ? $docente->nombre . ' ' . $docente->primer_apellido . ' ' . $docente->segundo_apellido : '—' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-700">
                                {{ $docente?->DNI ?? ($docente?->NIE ?? '—') }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $docente?->email ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $docente?->telefono ?? '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                {{ $docente?->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($docente->fecha_nacimiento)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $docente?->nacionalidad ?? '—' }}</td>

                            <td class="px-4 py-3 text-center">
                                @if (strtoupper((string) $lopd) === 'SI')
                                    <span
                                        class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Sí</span>
                                @else
                                    <span
                                        class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">No</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">


                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-tabla.tabla>
            </x-interfaz.panel.tarjeta>
        </x-slot:docentes>
        <x-slot:calendario title="Calendario">
            <x-slot:calendario title="Calendario">
                <x-calendario.calendario :url="route('curso.dia.create', $curso)" :inicial="\Illuminate\Support\Carbon::parse($curso->fecha_inicio)->toDateString()" :eventos="[
                    [
                        'title' => $horario,
                        'daysOfWeek' => [1, 2, 3, 4, 5],
                        'startTime' => $horaInicio,
                        'endTime' => $horaFinal,
                        'startRecur' => \Illuminate\Support\Carbon::parse($curso->fecha_inicio)->toDateString(),
                        'endRecur' => \Illuminate\Support\Carbon::parse($curso->fecha_fin)->addDay()->toDateString(),
                    ],
                ]" />
            </x-slot:calendario>
            <x-slot:pedidos title="Pedidos">
                <x-interfaz.panel.tarjeta titulo="Peticiones de logística">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm text-slate-500">{{ $curso->peticionesLogistica->count() }} peticiones</p>
                        <a href="{{ route('curso.logistica.store', $curso) }}"
                            class="inline-flex items-center rounded-lg bg-teal-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-900">
                            + Nueva petición
                        </a>
                    </div>

                    @if (session('ok'))
                        <div
                            class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                            {{ session('ok') }}
                        </div>
                    @endif

                    <x-tabla.tabla vacio="Todavía no hay peticiones de logística en este curso." :columnas="[
                        'nombre',
                        'cantidad',
                        'Fecha',
                        'Fecha necesaria',
                        'Solicitante',
                        'Centro',
                        ['Estado', 'center'],
                        ['Acciones', 'center'],
                    ]">

                        @foreach ($curso->peticionesLogistica as $peticion)
                            @php $linea = $peticion->peticionProductos->first(); @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                    {{ $linea?->producto ?? '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                    {{ $linea?->cantidad ?? '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                    {{ $peticion->fecha?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                    {{ $peticion->fecha_necesaria?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ $peticion->solicitante ? $peticion->solicitante->nombre . ' ' . $peticion->solicitante->primer_apellido : '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $peticion->centro?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                        {{ $peticion->estado ? ucfirst(strtolower(str_replace('_', ' ', $peticion->estado->value))) : '—' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </x-tabla.tabla>
                    @if ($curso->peticionesLogistica->isNotEmpty())
                        <form method="POST" action="{{ route('curso.logistica.enviar', $curso) }}"
                            class="mt-4 flex justify-end">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center rounded-lg bg-teal-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-900">
                                Enviar productos
                            </button>
                        </form>
                    @endif
                </x-interfaz.panel.tarjeta>
            </x-slot:pedidos>
    </x-interfaz.layout.pestanas>
</x-interfaz.layout.app>
