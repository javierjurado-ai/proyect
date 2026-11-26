@php
    $euros = fn ($n) => number_format((float) $n, 2, ',', '.') . ' €';
    $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('d/m/Y') : '—';
    $hoy   = today();

    // Estado de un curso según sus fechas
    $estadoCurso = function ($c) use ($hoy) {
        if ($c->fecha_fin->lt($hoy))    return ['Terminado', 'bg-stone-100 text-slate-600'];
        if ($c->fecha_inicio->gt($hoy)) return ['Próximo', 'bg-sky-100 text-sky-800'];
        return ['En marcha', 'bg-green-100 text-green-800'];
    };

    $textos = [
        'MENOS_PRIMARIA' => 'Menos que primaria', 'PRIMARIA' => 'Primaria', 'ESO_EGB' => 'ESO / EGB',
        'BACHILLERATO_FP_MEDIO' => 'Bachillerato / FP medio', 'CP_NIVEL_3' => 'Cert. profesional nivel 3',
        'FP_SUPERIOR' => 'FP superior', 'GRADO_DIPLOMATURA' => 'Grado / Diplomatura',
        'LICENCIATURA_MASTER' => 'Licenciatura / Máster', 'DOCTORADO' => 'Doctorado', 'OTRAS' => 'Otras',
        'DIRECTIVO' => 'Directivo', 'MANDO_INTERMEDIO' => 'Mando intermedio', 'TECNICO' => 'Técnico',
        'TRABAJADOR_CUALIFICADO' => 'Trabajador cualificado', 'TRABAJADOR_BAJA_CUALIFICACION' => 'Baja cualificación',
    ];

    $cursos     = $empresa->cursosGrupo;
    $creditos   = $empresa->creditosFormativos;
    $centros    = $empresa->centros;
    $costes     = $empresa->costes->keyBy('curso_id');
    $rlt        = $empresa->informesRlt->keyBy('curso_id');
    $costeTotal = $empresa->costes->sum(fn ($c) => $c->total);

    $porcentajeActual = $creditoActual && (float) $creditoActual->importe_total > 0
        ? round(100 * (float) $creditoActual->importe_consumido / (float) $creditoActual->importe_total)
        : null;
@endphp

<x-interfaz.layout.app :titulo="$empresa->nombre" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera :titulo="$empresa->nombre" subtitulo="CIF {{ $empresa->cif }}">
        <x-slot:acciones>
            <a href="{{ route('empresa.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver a empresas
            </a>
            <a href="{{ route('empresa.editar', $empresa->id) }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Editar empresa
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Indicadores --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Trabajadores" :valor="$empresa->total_trabajadores ?? '—'" />
        <x-interfaz.panel.metrica titulo="Cursos" :valor="$cursos->count()"
            :detalle="$cursos->filter(fn ($c) => $c->fecha_inicio->lte($hoy) && $c->fecha_fin->gte($hoy))->count() . ' en marcha'" />
        <x-interfaz.panel.metrica titulo="Alumnos formados" :valor="$empresa->cursoAlumnos()->distinct()->count('persona_id')"
            detalle="personas distintas" />

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <p class="text-sm text-slate-600">Crédito {{ $hoy->year }}</p>
            @if ($creditoActual)
                <p class="mt-2 text-3xl font-bold text-marca-950">{{ $porcentajeActual }} %</p>
                <p class="mt-2 text-xs text-slate-600">{{ $euros($creditoActual->importe_consumido) }} de {{ $euros($creditoActual->importe_total) }}</p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-200" role="img" aria-label="{{ $porcentajeActual }} % consumido">
                    <div class="h-full rounded-full {{ $porcentajeActual >= 80 ? 'bg-amber-500' : 'bg-emerald-600' }}" style="width: {{ min(100, $porcentajeActual) }}%"></div>
                </div>
            @else
                <p class="mt-2 text-lg font-bold text-amber-700">Sin registrar</p>
                <p class="mt-2 text-xs text-slate-600">No hay crédito formativo para este año</p>
            @endif
        </div>
    </div>

    <x-interfaz.layout.pestanas>

        {{-- Información --}}
        <x-slot:informacion title="Información">
            <x-interfaz.panel.tarjeta titulo="Datos de la empresa">
                <dl class="grid grid-cols-2 gap-4 text-sm md:grid-cols-3">
                    <div>
                        <dt class="text-slate-500">Nombre</dt>
                        <dd class="font-semibold">{{ $empresa->nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">CIF</dt>
                        <dd class="font-mono">{{ $empresa->cif }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Total trabajadores</dt>
                        <dd>{{ $empresa->total_trabajadores ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Centros propios</dt>
                        <dd>{{ $centros->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Coste total de formación</dt>
                        <dd>{{ $costes->isEmpty() ? '—' : $euros($costeTotal) }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Informes RLT</dt>
                        <dd>{{ $rlt->count() }} de {{ $cursos->count() }} cursos</dd>
                    </div>
                </dl>
            </x-interfaz.panel.tarjeta>
        </x-slot:informacion>

        {{-- Cursos en los que participa (grupo_empresa) --}}
        <x-slot:cursos title="Cursos ({{ $cursos->count() }})">
            <x-interfaz.panel.tarjeta titulo="Cursos en los que participa">
                <x-tabla.tabla vacio="Esta empresa todavía no participa en ningún curso."
                    :columnas="['Acción / Grupo', 'Formación', 'Fechas', 'Modalidad', 'Estado', ['Coste', 'right'], ['RLT', 'center']]">
                    @foreach ($cursos as $c)
                        @php
                            [$textoEstado, $colorEstado] = $estadoCurso($c);
                            $coste = $costes->get($c->id);
                            $informe = $rlt->get($c->id);
                            $mod = $c->modalidad?->value;
                        @endphp
                        <tr class="cursor-pointer hover:bg-stone-50" onclick="window.location='{{ route('curso.visualizar', $c) }}'">
                            <td class="px-4 py-3 font-mono text-slate-700">{{ $c->codigo_accion }}/{{ $c->codigo_grupo }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $c->formacion?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $fecha($c->fecha_inicio) }} – {{ $fecha($c->fecha_fin) }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $mod === 'TELEFORMACION' ? 'Teleformación' : ($mod ? ucfirst(strtolower($mod)) : '—') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colorEstado }}">{{ $textoEstado }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-slate-700">{{ $coste ? $euros($coste->total) : '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($informe)
                                    @php $favorable = ($informe->valor_informe?->value ?? $informe->valor_informe) === 'F'; @endphp
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $favorable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"
                                          title="{{ $favorable ? 'Favorable' : 'Desfavorable' }}">
                                        {{ $favorable ? 'F' : 'D' }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-tabla.tabla>
            </x-interfaz.panel.tarjeta>
        </x-slot:cursos>

        {{-- Crédito formativo por año --}}
        <x-slot:creditos title="Créditos">
            <x-interfaz.panel.tarjeta titulo="Crédito formativo por año"
                :enlace="in_array($rol, ['ADMINISTRADOR', 'COMERCIAL'], true) ? route('credito.create', ['empresa_id' => $empresa->id]) : null"
                texto-enlace="Registrar crédito">
                <x-tabla.tabla vacio="Esta empresa no tiene créditos formativos registrados."
                    :columnas="['Año', ['Importe total', 'right'], ['Consumido', 'right'], ['Disponible', 'right'], 'Uso']">
                    @foreach ($creditos as $cr)
                        @php
                            $total = (float) $cr->importe_total;
                            $consumido = (float) $cr->importe_consumido;
                            $pct = $total > 0 ? round(100 * $consumido / $total) : 0;
                        @endphp
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $cr->año }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-slate-700">{{ $euros($total) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-slate-700">{{ $euros($consumido) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-right font-semibold {{ $total - $consumido > 0 ? 'text-emerald-700' : 'text-slate-500' }}">{{ $euros($total - $consumido) }}</td>
                            <td class="w-48 px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-stone-200" role="img" aria-label="{{ $pct }} % consumido">
                                        <div class="h-full rounded-full {{ $pct >= 80 ? 'bg-amber-500' : 'bg-emerald-600' }}" style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                    <span class="w-10 text-right text-xs text-slate-600">{{ $pct }} %</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-tabla.tabla>
            </x-interfaz.panel.tarjeta>
        </x-slot:creditos>

        {{-- Centros que pertenecen a la empresa --}}
        <x-slot:centros title="Centros ({{ $centros->count() }})">
            <x-interfaz.panel.tarjeta titulo="Centros de la empresa">
                <x-tabla.tabla vacio="Esta empresa no tiene centros propios."
                    :columnas="['Nombre', 'Tipo', 'Dirección', 'Teléfono', 'Email', ['Aulas', 'right']]">
                    @foreach ($centros as $ce)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $ce->nombre }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ ucfirst(strtolower($ce->tipo_centro?->value ?? '')) ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $ce->direccion }}
                                <span class="block text-xs text-slate-500">{{ $ce->codigo_postal }} {{ $ce->municipio }} ({{ $ce->provincia }})</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $ce->telefono }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $ce->email }}</td>
                            <td class="px-4 py-3 text-right text-slate-700">{{ $ce->numero_total_aula }}</td>
                        </tr>
                    @endforeach
                </x-tabla.tabla>
            </x-interfaz.panel.tarjeta>
        </x-slot:centros>

        {{-- Alumnos de la empresa: solo administrador y coordinador.
             Para el resto el slot queda vacío y la pestaña no aparece. --}}
        <x-slot:alumnos title="Alumnos ({{ $alumnos->count() }})">
            @if ($puedeVerAlumnos)
                <x-interfaz.panel.tarjeta titulo="Alumnos enviados por la empresa">
                    <x-tabla.tabla vacio="Esta empresa todavía no tiene alumnos inscritos."
                        :columnas="['Alumno', 'DNI / NIE', 'Curso', 'Estudios', 'Categoría', ['Diploma', 'center']]">
                        @foreach ($alumnos as $inscripcion)
                            @php $p = $inscripcion->persona; $c = $inscripcion->curso; @endphp
                            <tr class="hover:bg-stone-50">
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ $p?->primer_apellido }} {{ $p?->segundo_apellido }}, {{ $p?->nombre }}
                                    <span class="block text-xs font-normal text-slate-500">{{ $p?->email }}</span>
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-700">{{ $p?->DNI ?? $p?->NIE ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">
                                    @if ($c)
                                        <a href="{{ route('curso.visualizar', $c) }}" class="font-semibold text-marca-900 underline underline-offset-2 hover:text-marca-700">
                                            {{ $c->formacion?->nombre ?? $c->codigo_accion . '/' . $c->codigo_grupo }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $textos[$inscripcion->nivel_estudio] ?? $inscripcion->nivel_estudio }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $textos[$inscripcion->categoria_profesional] ?? $inscripcion->categoria_profesional }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $inscripcion->diploma === 'SI' ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-slate-600' }}">
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
