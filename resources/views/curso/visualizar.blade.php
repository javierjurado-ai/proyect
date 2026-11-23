<x-interfaz.layout.app titulo="Curso {{ $curso->codigo_accion }}/{{ $curso->codigo_grupo }}" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="{{ $curso->formacion?->nombre ?? 'Curso' }}"
        subtitulo="Acción {{ $curso->codigo_accion }} · Grupo {{ $curso->codigo_grupo }}">
        <x-slot:acciones>
            <a href="{{ route('curso.crearTablaCurso') }}"
                class="inline-flex items-center rounded-lg border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-50">
                Volver
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @php
        $formacion = $curso->formacion;
        $presupuesto = $curso->presupuesto;
        $peticionLogistica = $curso->peticionLogistica;
        $modalidad = $curso->modalidad->value ?? $curso->modalidad;
    @endphp

    <x-interfaz.layout.pestanas>

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
                        <dd>{{ $modalidad === 'TELEFORMACION' ? 'Teleformación' : ($modalidad ? ucfirst(strtolower($modalidad)) : '—') }}</dd>
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
                        <dd>
                            @if ($curso->hora_inicio)
                                {{ substr($curso->hora_inicio, 0, 5) }}{{ $curso->hora_final ? ' – ' . substr($curso->hora_final, 0, 5) : '' }}
                            @else
                                —
                            @endif
                        </dd>
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
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
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
                            <dd>{{ $peticionLogistica->estado ? ucfirst(strtolower(str_replace('_', ' ', $peticionLogistica->estado->value))) : '—' }}</dd>
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
        {{-- Solo administrador y coordinador. Para el resto el slot queda vacío
             y el componente de pestañas no muestra la pestaña. --}}
        <x-slot:alumnos title="Alumnos ({{ $alumnos->count() }})">
            @if ($puedeVerAlumnos)
                @php
                    $textos = [
                        'MENOS_PRIMARIA' => 'Menos que primaria', 'PRIMARIA' => 'Primaria', 'ESO_EGB' => 'ESO / EGB',
                        'BACHILLERATO_FP_MEDIO' => 'Bachillerato / FP medio', 'CP_NIVEL_3' => 'Cert. profesional nivel 3',
                        'FP_SUPERIOR' => 'FP superior', 'GRADO_DIPLOMATURA' => 'Grado / Diplomatura',
                        'LICENCIATURA_MASTER' => 'Licenciatura / Máster', 'DOCTORADO' => 'Doctorado', 'OTRAS' => 'Otras',
                        'DIRECTIVO' => 'Directivo', 'MANDO_INTERMEDIO' => 'Mando intermedio', 'TECNICO' => 'Técnico',
                        'TRABAJADOR_CUALIFICADO' => 'Trabajador cualificado', 'TRABAJADOR_BAJA_CUALIFICACION' => 'Baja cualificación',
                    ];
                @endphp

                <x-interfaz.panel.tarjeta titulo="Alumnos inscritos">
                    <x-tabla.tabla vacio="Este curso todavía no tiene alumnos inscritos."
                        :columnas="['Alumno', 'DNI / NIE', 'Contacto', 'Empresa', 'Estudios', 'Categoría', ['Diploma', 'center']]">
                        @foreach ($alumnos as $inscripcion)
                            @php $p = $inscripcion->persona; @endphp
                            <tr class="hover:bg-stone-50">
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ $p?->primer_apellido }} {{ $p?->segundo_apellido }}, {{ $p?->nombre }}
                                    @if ($inscripcion->certificado_discapacidad === 'SI')
                                        <span class="ml-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800">Discapacidad</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-700">{{ $p?->DNI ?? $p?->NIE ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">
                                    <span class="block">{{ $p?->email ?? '—' }}</span>
                                    <span class="block text-xs text-slate-500">{{ $p?->telefono }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $inscripcion->empresa?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $textos[$inscripcion->nivel_estudio] ?? $inscripcion->nivel_estudio }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $textos[$inscripcion->categoria_profesional] ?? $inscripcion->categoria_profesional }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        {{ $inscripcion->diploma === 'SI' ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-slate-600' }}">
                                        {{ $inscripcion->diploma === 'SI' ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </x-tabla.tabla>
                </x-interfaz.panel.tarjeta>
            @endif
        </x-slot:alumnos>
        

    </x-interfaz.layout.pestanas>

</x-interfaz.layout.app>