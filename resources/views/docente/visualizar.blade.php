@php
    $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('d/m/Y') : '—';
    $horas = fn ($h) => rtrim(rtrim(number_format((float) $h, 2, ',', '.'), '0'), ',');
    $euros = fn ($n) => number_format((float) $n, 2, ',', '.') . ' €';
    $hora  = fn ($h) => $h ? substr($h, 0, 5) : '—';
    $hoy   = today();
    $nombreCompleto = trim($persona->nombre . ' ' . $persona->primer_apellido . ' ' . $persona->segundo_apellido);

    $estado = function ($a) use ($hoy) {
        if ($a->fecha_fin->lt($hoy))    return ['Terminado', 'bg-stone-100 text-slate-600'];
        if ($a->fecha_inicio->gt($hoy)) return ['Próximo', 'bg-sky-100 text-sky-800'];
        return ['En marcha', 'bg-green-100 text-green-800'];
    };

    $ordenDias = ['LUNES' => 1, 'MARTES' => 2, 'MIERCOLES' => 3, 'JUEVES' => 4, 'VIERNES' => 5, 'SABADO' => 6, 'DOMINGO' => 7];
    $textoDia  = ['LUNES' => 'Lunes', 'MARTES' => 'Martes', 'MIERCOLES' => 'Miércoles', 'JUEVES' => 'Jueves', 'VIERNES' => 'Viernes', 'SABADO' => 'Sábado', 'DOMINGO' => 'Domingo'];
    $textoTutoria = ['CORREO_ELECTRONICO' => 'Correo electrónico', 'VIDEOCONFERENCIA' => 'Videoconferencia', 'FORO' => 'Foro', 'OTRAS' => 'Otras'];
    $valor = fn ($v) => $v instanceof \BackedEnum ? $v->value : $v;

    // El IBAN no se enseña entero: solo país y últimos 4 dígitos
    $ibanOculto = function ($iban) {
        $iban = preg_replace('/\s+/', '', (string) $iban);
        return strlen($iban) > 8 ? substr($iban, 0, 4) . ' •••• •••• ' . substr($iban, -4) : '—';
    };

    $genero = $valor($persona->genero);
    $enMarcha = $asignaciones->filter(fn ($a) => $a->fecha_inicio->lte($hoy) && $a->fecha_fin->gte($hoy));

    // Eventos del calendario: un evento por tramo horario de cada día de cada curso que imparte
    // (solo los días dentro de las fechas en que imparte ese curso)
    $eventosCalendario = $asignaciones
        ->filter(fn ($a) => $a->curso)
        ->flatMap(function ($a) {
            $nombreCurso = $a->curso->formacion?->nombre ?? 'Curso';

            return $a->curso->calendario
                ->filter(fn ($dia) => $dia->fecha->between($a->fecha_inicio, $a->fecha_fin))
                ->flatMap(function ($dia) use ($nombreCurso) {
                    $dia_ = $dia->fecha->toDateString();

                    return collect([[$dia->hora_inicio1, $dia->hora_fin1], [$dia->hora_inicio2, $dia->hora_fin2]])
                        ->filter(fn ($t) => $t[0] && $t[1])
                        ->map(fn ($t) => [
                            'title' => substr($t[0], 0, 5) . ' – ' . substr($t[1], 0, 5) . ' ' . $nombreCurso,
                            'start' => $dia_ . 'T' . substr($t[0], 0, 5),
                            'end'   => $dia_ . 'T' . substr($t[1], 0, 5),
                        ]);
                });
        })
        ->values()
        ->all();

    $inicialCalendario = ($enMarcha->first() ?? $asignaciones->first())?->fecha_inicio?->toDateString();
@endphp

<x-interfaz.layout.app :titulo="$nombreCompleto" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera :titulo="$nombreCompleto" subtitulo="Docente · {{ $persona->DNI ?: $persona->NIE }}">
        <x-slot:acciones>
            <a href="{{ route('docente.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver a docentes
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-interfaz.panel.metrica titulo="Cursos" :valor="$asignaciones->count()" :detalle="$enMarcha->count() . ' en marcha'" />
        <x-interfaz.panel.metrica titulo="Horas asignadas" :valor="$horas($asignaciones->sum('numero_horas')) . ' h'" detalle="en todos sus cursos" />
        <x-interfaz.panel.metrica titulo="Empresas" :valor="$asignaciones->pluck('empresa_id')->unique()->count()" detalle="con las que ha trabajado" />
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <x-interfaz.panel.tarjeta titulo="Datos personales">
            <dl class="grid grid-cols-1 gap-3 text-sm">
                <div><dt class="text-slate-500">Nombre completo</dt><dd class="font-semibold">{{ $nombreCompleto }}</dd></div>
                <div><dt class="text-slate-500">{{ $persona->DNI ? 'DNI' : 'NIE' }}</dt><dd class="font-mono">{{ $persona->DNI ?: $persona->NIE ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Fecha de nacimiento</dt><dd>{{ $fecha($persona->fecha_nacimiento) }}</dd></div>
                <div><dt class="text-slate-500">Género</dt><dd>{{ $genero ? ucfirst(strtolower($genero)) : '—' }}</dd></div>
                <div><dt class="text-slate-500">Email</dt><dd>{{ $persona->email }}</dd></div>
                <div><dt class="text-slate-500">Teléfono</dt><dd>{{ $persona->telefono }}</dd></div>
                <div><dt class="text-slate-500">Dirección</dt><dd>{{ $persona->direccion }}</dd></div>
                <div><dt class="text-slate-500">Nº Seguridad Social</dt><dd class="font-mono">{{ $persona->numero_seguridad_social }}</dd></div>
            </dl>
        </x-interfaz.panel.tarjeta>

        <x-interfaz.panel.tarjeta titulo="Cursos que imparte" class="lg:col-span-2">
            <x-tabla.tabla vacio="No imparte ningún curso."
                :columnas="['Curso', 'Fechas', 'Estado', 'Empresa', ['Horas', 'right'], 'Contrato']">
                @foreach ($asignaciones as $a)
                    @php [$textoEstado, $colorEstado] = $estado($a); @endphp
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3">
                            @if ($a->curso)
                                <a href="{{ route('curso.visualizar', $a->curso) }}" class="font-semibold text-marca-900 hover:underline">
                                    {{ $a->curso->formacion?->nombre ?? 'Curso' }}
                                </a>
                                <span class="block font-mono text-xs text-slate-500">{{ $a->curso->codigo_accion }}/{{ $a->curso->codigo_grupo }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $fecha($a->fecha_inicio) }} – {{ $fecha($a->fecha_fin) }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colorEstado }}">{{ $textoEstado }}</span></td>
                        <td class="px-4 py-3 text-slate-700">{{ $a->empresa?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-slate-700">{{ $horas($a->numero_horas) }} h</td>
                        <td class="px-4 py-3 text-slate-700">
                            <span class="block">{{ ucfirst(strtolower($a->tipo_contrato)) }}</span>
                            <span class="block text-xs text-slate-500">{{ ucfirst(strtolower($a->catagoria)) }} · {{ $a->convenio }}</span>
                        </td>
                    </tr>
                @endforeach
            </x-tabla.tabla>
        </x-interfaz.panel.tarjeta>
    </div>

    {{-- Calendario: igual que en la ficha del alumno --}}
    <x-interfaz.panel.tarjeta titulo="Calendario" class="mb-6">
        <x-calendario.calendario :inicial="$inicialCalendario" :eventos="$eventosCalendario" />
    </x-interfaz.panel.tarjeta>

    {{-- Detalle por curso: horario, tutorías y retribución --}}
    <x-interfaz.panel.tarjeta titulo="Horario, tutorías y retribución por curso">
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($asignaciones as $a)
                @php
                    $h = ($horarios[$a->id] ?? collect())->sortBy(fn ($x) => $ordenDias[$valor($x->dia_semana)] ?? 9);
                    $t = $tutorias[$a->id] ?? collect();
                    $r = ($retribuciones[$a->id] ?? collect())->first();
                @endphp
                <article class="rounded-lg border border-stone-200 p-4">
                    <h3 class="mb-3 font-bold text-marca-950">
                        {{ $a->curso?->formacion?->nombre ?? 'Curso' }}
                        <span class="font-mono text-xs font-normal text-slate-500">{{ $a->curso?->codigo_accion }}/{{ $a->curso?->codigo_grupo }}</span>
                    </h3>

                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-marca-800">Horario semanal</p>
                    @if ($h->isEmpty())
                        <p class="mb-3 text-sm text-slate-500">Sin horario registrado.</p>
                    @else
                        <ul class="mb-3 space-y-1 text-sm text-slate-700">
                            @foreach ($h as $fila)
                                <li class="flex justify-between gap-3">
                                    <span>{{ $textoDia[$valor($fila->dia_semana)] ?? $valor($fila->dia_semana) }}</span>
                                    <span class="whitespace-nowrap">{{ $hora($fila->hora_inicio) }}–{{ $hora($fila->hora_fin) }} · {{ ucfirst(strtolower($valor($fila->turno))) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-marca-800">Tutorías</p>
                    <p class="mb-3 text-sm text-slate-700">
                        {{ $t->map(fn ($x) => ($textoTutoria[$valor($x->tipo_tutoria)] ?? $valor($x->tipo_tutoria)) . ($x->descripcion ? ' (' . $x->descripcion . ')' : ''))->implode(', ') ?: 'Sin tutorías registradas.' }}
                    </p>

                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-marca-800">Retribución</p>
                    @if ($r)
                        <p class="text-sm text-slate-700">
                            {{ ucfirst(strtolower($valor($r->tipo))) }}: <span class="font-semibold">{{ $euros($r->importe) }}</span>
                            · Seguridad Social {{ $horas($r->seguridad_social) }}
                            <span class="block text-xs text-slate-500">Desde el {{ $fecha($r->fecha) }} · IBAN {{ $ibanOculto($a->IBAN) }}</span>
                        </p>
                    @else
                        <p class="text-sm text-slate-500">Sin retribución registrada.</p>
                    @endif
                </article>
            @endforeach
        </div>
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>