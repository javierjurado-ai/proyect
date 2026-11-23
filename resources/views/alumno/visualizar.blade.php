@php
    $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('d/m/Y') : '—';
    $hoy   = today();
    $nombreCompleto = trim($persona->nombre . ' ' . $persona->primer_apellido . ' ' . $persona->segundo_apellido);

    $textos = [
        'MENOS_PRIMARIA' => 'Menos que primaria', 'PRIMARIA' => 'Primaria', 'ESO_EGB' => 'ESO / EGB',
        'BACHILLERATO_FP_MEDIO' => 'Bachillerato / FP medio', 'CP_NIVEL_3' => 'Cert. profesional nivel 3',
        'FP_SUPERIOR' => 'FP superior', 'GRADO_DIPLOMATURA' => 'Grado / Diplomatura',
        'LICENCIATURA_MASTER' => 'Licenciatura / Máster', 'DOCTORADO' => 'Doctorado', 'OTRAS' => 'Otras',
        'DIRECTIVO' => 'Directivo', 'MANDO_INTERMEDIO' => 'Mando intermedio', 'TECNICO' => 'Técnico',
        'TRABAJADOR_CUALIFICADO' => 'Trabajador cualificado', 'TRABAJADOR_BAJA_CUALIFICACION' => 'Baja cualificación',
    ];

    $estado = function ($c) use ($hoy) {
        if (! $c) return ['—', 'bg-stone-100 text-slate-600'];
        if ($c->fecha_fin->lt($hoy))    return ['Terminado', 'bg-stone-100 text-slate-600'];
        if ($c->fecha_inicio->gt($hoy)) return ['Próximo', 'bg-sky-100 text-sky-800'];
        return ['En marcha', 'bg-green-100 text-green-800'];
    };

    $genero = $persona->genero->value ?? $persona->genero;
    $lopd   = $persona->LOPD->value ?? $persona->LOPD;
    $enMarcha = $inscripciones->filter(fn ($i) => $i->curso && $i->curso->fecha_inicio->lte($hoy) && $i->curso->fecha_fin->gte($hoy))->count();
    // Los certificados se piden por inscripción: se marca si alguna los tiene
    $certificados = collect([
        'certificado_discapacidad'              => 'Discapacidad',
        'certificado_afectado_violencia_genero' => 'Víctima de violencia de género',
        'certificado_afectado_terrorismo'       => 'Afectado por terrorismo',
    ])->filter(fn ($t, $campo) => $inscripciones->contains($campo, 'SI'));
@endphp

<x-interfaz.layout.app :titulo="$nombreCompleto" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera :titulo="$nombreCompleto" subtitulo="Alumno · {{ $persona->DNI ?: $persona->NIE }}">
        <x-slot:acciones>
            <a href="{{ route('alumno.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver a alumnos
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-interfaz.panel.metrica titulo="Cursos" :valor="$inscripciones->count()" :detalle="$enMarcha . ' en marcha'" />
        <x-interfaz.panel.metrica titulo="Diplomas" :valor="$inscripciones->where('diploma', 'SI')->count()" detalle="cursos terminados con diploma" />
        <x-interfaz.panel.metrica titulo="Empresas" :valor="$inscripciones->pluck('empresa_id')->unique()->count()" detalle="por las que se ha formado" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-interfaz.panel.tarjeta titulo="Datos personales" class="lg:col-span-1">
            <dl class="grid grid-cols-1 gap-3 text-sm">
                <div><dt class="text-slate-500">Nombre completo</dt><dd class="font-semibold">{{ $nombreCompleto }}</dd></div>
                <div><dt class="text-slate-500">{{ $persona->DNI ? 'DNI' : 'NIE' }}</dt><dd class="font-mono">{{ $persona->DNI ?: $persona->NIE ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Fecha de nacimiento</dt>
                    <dd>{{ $fecha($persona->fecha_nacimiento) }} @if ($persona->fecha_nacimiento) <span class="text-slate-500">({{ $persona->fecha_nacimiento->age }} años)</span> @endif</dd></div>
                <div><dt class="text-slate-500">Género</dt><dd>{{ $genero ? ucfirst(strtolower($genero)) : '—' }}</dd></div>
                <div><dt class="text-slate-500">Nacionalidad</dt><dd>{{ $persona->nacionalidad }}</dd></div>
                <div><dt class="text-slate-500">Email</dt><dd>{{ $persona->email }}</dd></div>
                <div><dt class="text-slate-500">Teléfono</dt><dd>{{ $persona->telefono }}</dd></div>
                <div><dt class="text-slate-500">Dirección</dt><dd>{{ $persona->direccion }}</dd></div>
                <div><dt class="text-slate-500">Nº Seguridad Social</dt><dd class="font-mono">{{ $persona->numero_seguridad_social }}</dd></div>
                <div><dt class="text-slate-500">LOPD</dt>
                    <dd><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $lopd === 'SI' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $lopd === 'SI' ? 'Aceptada' : 'No aceptada' }}</span></dd></div>
                @if ($certificados->isNotEmpty())
                    <div><dt class="text-slate-500">Certificados</dt>
                        <dd class="flex flex-wrap gap-1">
                            @foreach ($certificados as $texto)
                                <span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-800">{{ $texto }}</span>
                            @endforeach
                        </dd></div>
                @endif
            </dl>
        </x-interfaz.panel.tarjeta>

        <x-interfaz.panel.tarjeta titulo="Cursos en los que está inscrito" class="lg:col-span-2">
            <x-tabla.tabla vacio="No está inscrito en ningún curso."
                :columnas="['Curso', 'Fechas', 'Estado', 'Empresa', 'Estudios / Categoría', ['Diploma', 'center']]">
                @foreach ($inscripciones as $i)
                    @php [$textoEstado, $colorEstado] = $estado($i->curso); @endphp
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3">
                            @if ($i->curso)
                                <a href="{{ route('curso.visualizar', $i->curso) }}" class="font-semibold text-marca-900 hover:underline">
                                    {{ $i->curso->formacion?->nombre ?? 'Curso' }}
                                </a>
                                <span class="block font-mono text-xs text-slate-500">{{ $i->curso->codigo_accion }}/{{ $i->curso->codigo_grupo }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $fecha($i->curso?->fecha_inicio) }} – {{ $fecha($i->curso?->fecha_fin) }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colorEstado }}">{{ $textoEstado }}</span></td>
                        <td class="px-4 py-3 text-slate-700">{{ $i->empresa?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            <span class="block">{{ $textos[$i->nivel_estudio] ?? $i->nivel_estudio }}</span>
                            <span class="block text-xs text-slate-500">{{ $textos[$i->categoria_profesional] ?? $i->categoria_profesional }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $i->diploma === 'SI' ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-slate-600' }}">
                                {{ $i->diploma === 'SI' ? 'Sí' : 'No' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-tabla.tabla>
        </x-interfaz.panel.tarjeta>
    </div>

</x-interfaz.layout.app>
