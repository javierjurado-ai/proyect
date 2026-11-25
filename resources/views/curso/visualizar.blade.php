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
        <x-slot:productos title="Productos">
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
        </x-slot:productos>
        <x-slot:calendario title="Calendario">
            <x-interfaz.panel.tarjeta titulo="Calendario del curso">
                @if (session('ok'))
                    <div role="status" class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('ok') }}
                    </div>
                @endif

                <div id="calendario-curso">
                    {{-- Barra de acciones: actúa sobre el día seleccionado en el calendario --}}
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-stone-200 bg-stone-50 px-4 py-3">
                        <div class="min-w-0 text-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Día seleccionado</p>
                            <p id="dia-seleccionado" class="truncate font-semibold text-marca-950">
                                <span class="font-normal text-stone-500">Ninguno · pulsa un día con sesión</span>
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('curso.crearTablaCurso') }}"
                                class="inline-flex items-center gap-1 rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-200/60">
                                ← Atrás
                            </a>

                            <form id="form-cancelar-dia" method="POST" action="">
                                @csrf
                                @method('DELETE')
                                <button type="submit" id="boton-cancelar-dia" disabled
                                    class="inline-flex items-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-white">
                                    Cancelar sesión
                                </button>
                            </form>

                            <a id="boton-modificar-dia" href="#" aria-disabled="true"
                                class="pointer-events-none inline-flex items-center rounded-lg bg-marca-800 px-4 py-2 text-sm font-semibold text-white opacity-40 hover:bg-marca-900">
                                Modificar
                            </a>
                        </div>
                    </div>

                    <div class="mb-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-stone-500">
                        <span class="inline-flex items-center gap-2">
                            <span class="h-3 w-3 rounded-sm border-l-[3px] border-marca-700 bg-marca-100"></span>
                            Sesión ({{ $diasConSesion }} {{ $diasConSesion === 1 ? 'día' : 'días' }})
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <span class="h-3 w-3 rounded-sm bg-marca-50 ring-1 ring-marca-100"></span>
                            Periodo del curso
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-marca-900"></span>
                            Hoy
                        </span>
                        <span class="ml-auto text-xs">Pulsa un día con sesión para seleccionarlo, o un día vacío para añadirlo</span>
                    </div>

                    @error('generar')
                        <div role="alert" class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
                    @enderror

                    @if ($diasConSesion === 0)
                        @if ($horaInicio && $horaFinal && $curso->fecha_inicio && $curso->fecha_fin)
                            {{-- Tiene horario general, pero todavía no hay días concretos en el calendario --}}
                            <form method="POST" action="{{ route('curso.calendario.generar', $curso) }}"
                                class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-marca-100 bg-marca-50 px-4 py-3 text-sm">
                                @csrf
                                <div class="text-marca-950">
                                    <p class="font-semibold">El curso tiene horario {{ $horario }}, del
                                        {{ \Illuminate\Support\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') }} al
                                        {{ \Illuminate\Support\Carbon::parse($curso->fecha_fin)->format('d/m/Y') }}.</p>
                                    <p class="text-marca-800">Genera los días para verlos en el calendario. Después podrás modificar o cancelar cada uno.</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <label for="modo-generar" class="sr-only">Qué días hay clase</label>
                                    <select id="modo-generar" name="modo"
                                        class="rounded-lg border border-marca-100 bg-white px-3 py-2 text-sm text-marca-950 focus:outline-none focus:ring-2 focus:ring-marca-700">
                                        <option value="lv">De lunes a viernes</option>
                                        <option value="todos">Todos los días</option>
                                        <option value="alterno">Un día sí y otro no</option>
                                    </select>
                                    <button type="submit"
                                        class="inline-flex items-center rounded-lg bg-marca-800 px-4 py-2 font-semibold text-white hover:bg-marca-900">
                                        Generar días
                                    </button>
                                </div>
                            </form>
                        @else
                            <p class="mb-4 rounded-lg border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                                Este curso todavía no tiene horario. Haz clic en cualquier día del periodo
                                (también sábados o domingos) para añadirlo, o indica el horario en los datos del curso.
                            </p>
                        @endif
                    @endif

                    <x-calendario.calendario seleccionable :url="route('curso.dia.create', $curso)"
                        :inicial="$curso->fecha_inicio ? \Illuminate\Support\Carbon::parse($curso->fecha_inicio)->toDateString() : null"
                        :eventos="$eventosCalendario" />
                </div>
            </x-interfaz.panel.tarjeta>

            @push('scripts')
                <script>
                    (() => {
                        const contenedor = document.getElementById('calendario-curso');
                        const texto = document.getElementById('dia-seleccionado');
                        const modificar = document.getElementById('boton-modificar-dia');
                        const formCancelar = document.getElementById('form-cancelar-dia');
                        const cancelar = document.getElementById('boton-cancelar-dia');
                        let fechaTexto = '';

                        contenedor.addEventListener('calendario:seleccion', (e) => {
                            const { fecha, horario, editar, cancelar: urlCancelar } = e.detail;
                            const [a, m, d] = fecha.split('-').map(Number);
                            fechaTexto = new Date(a, m - 1, d)
                                .toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                            fechaTexto = fechaTexto.charAt(0).toUpperCase() + fechaTexto.slice(1);

                            texto.textContent = fechaTexto + (horario ? ' · ' + horario : '');

                            modificar.href = editar;
                            modificar.removeAttribute('aria-disabled');
                            modificar.classList.remove('pointer-events-none', 'opacity-40');

                            formCancelar.action = urlCancelar;
                            cancelar.disabled = false;
                        });

                        formCancelar.addEventListener('submit', (e) => {
                            if (!formCancelar.action || cancelar.disabled ||
                                !confirm('¿Cancelar la sesión del ' + fechaTexto + '?\nEl día se quitará del calendario del curso.')) {
                                e.preventDefault();
                            }
                        });
                    })();
                </script>
            @endpush
        </x-slot:calendario>
    </x-interfaz.layout.pestanas>

</x-interfaz.layout.app>