<x-interfaz.layout.app titulo="Curso {{ $curso->codigo_curso }}" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="{{ $curso->formacion?->nombre ?? 'Curso' }}"
        subtitulo="{{ $curso->codigo_curso }}">
        <x-slot:acciones>
            <a href="{{ route('curso.crearTablaCurso') }}"
                class="inline-flex items-center rounded-lg border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-50">
                Volver
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <div class="space-y-6">

        {{-- Datos del curso --}}
        <x-interfaz.panel.tarjeta titulo="Curso">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">Código del curso</dt>
                    <dd class="font-mono">{{ $curso->codigo_curso }}</dd>
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
                    @if ($curso->aula_virtual)
                        <dd class="text-xs text-slate-500">{{ $curso->aula_virtual }}</dd>
                    @endif
                </div>
            </dl>
        </x-interfaz.panel.tarjeta>

        {{-- Datos de la formación --}}
        <x-interfaz.panel.tarjeta titulo="Formación">
            @if ($curso->formacion)
                @php $formacion = $curso->formacion; @endphp
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
                        <dt class="text-slate-500">Modalidad</dt>
                        <dd>{{ $formacion->modalidad->value ?? ($formacion->modalidad ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Sector</dt>
                        <dd>{{ $formacion->sector ?? '—' }}</dd>
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
            @if ($curso->presupuesto)
                @php $presupuesto = $curso->presupuesto; @endphp
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
                        <dt class="text-slate-500">Horas Totales</dt>
                        <dd>{{ $formacion->modalidad->value ?? ($presupuesto->horas_totales ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Ubicacion de la Formación</dt>
                        <dd>{{ $presupuesto->ubicacion_formacion ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Objetivos</dt>
                        <dd>{{ $presupuesto->objetivos ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Contenidos</dt>
                        <dd>{{ $presupuesto->contenidos ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Importe Bonificado</dt>
                        <dd>{{ $presupuesto->importe_bonificado ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Fecha Bonificada</dt>
                        <dd>{{ $presupuesto->fecha_bonificado ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Estado</dt>
                        <dd>
                            <span
                                class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                {{ $presupuesto->activo ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-700' }}">
                                {{ $presupuesto->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-slate-500">Este curso no tiene presupuesto.</p>
            @endif
        </x-interfaz.panel.tarjeta>
        <x-interfaz.panel.tarjeta titulo="Logistica">
            @if ($curso->peticionLogistica)
                @php $peticionLogistica = $curso->peticionLogistica; @endphp
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Fecha</dt>
                        <dd class="font-semibold">{{ $peticionLogistica->fecha ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Estado</dt>
                        <dd class="font-mono">{{ $formacion->estado ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Ubicacion de la Formación</dt>
                        <dd>{{ $formacion->observaciones ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Estado</dt>
                        <dd>
                            <span
                                class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                {{ $peticionLogistica->activo ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-700' }}">
                                {{ $peticionLogistica->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-slate-500">Este curso no logistica.</p>
            @endif
        </x-interfaz.panel.tarjeta>
    </div>

</x-interfaz.layout.app>
