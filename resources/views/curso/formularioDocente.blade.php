@php
    // Variables que usa este parcial (vienen del ámbito de la vista que lo incluye):
    // $alumno, $editando, $curso, $cursos, $personas, $empresas,
    // $jornadas, $categoriasProfesionales, $nivelesEstudio, $siNo
    // Se incluye así: @include('alumno.formulario', ['incrustado' => true])
    $incrustado = $incrustado ?? false;
    $editando = $editando ?? false;
    $docente = $docente ?? null;
    // Si viene $prefijo (ej. 'alumnos[__ID__]') los campos se llaman alumnos[__ID__][erte], etc.
    $prefijo = $prefijo ?? null;
    $n = fn(string $campo) => $prefijo ? "{$prefijo}[{$campo}]" : $campo;

    $fecha = fn($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('Y-m-d') : '';
    $hora = fn($h) => $h ? substr($h, 0, 5) : '';
@endphp

<section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
    @unless ($incrustado)
        <form id="docente-form" method="POST"
            action="{{ $editando ? route('docente.update', $docente->id) : route('docente.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif
        @endunless

        @if ($incrustado && !$prefijo)
            <input type="hidden" name="curso_id" value="{{ $curso->id }}">
        @endif

        <fieldset class="mb-8">
            <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos del docente
            </legend>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @unless ($incrustado)
                    <x-formulario.selector name="curso_id" label="Curso *" placeholder="Busca un curso..." :options="$cursos"
                        contenedor="md:col-span-2" :value="$docente->curso_id ?? ''" />

                    <x-formulario.selector name="persona_id" label="Persona *" placeholder="Busca una persona..."
                        :options="$personas" contenedor="md:col-span-2" :value="$docente->persona_id ?? ''" />
                @endunless

                <x-formulario.selector :name="$n('empresa_id')" label="Empresa *" placeholder="Busca una empresa..."
                    :options="$empresas" contenedor="md:col-span-2" :value="$docente->empresa_id ?? ''" />
            </div>
        </fieldset>

        <fieldset class="mb-8">
            <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Información del Contrato
            </legend>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-formulario.seleccion :name="$n('categoria')" label="Categoria *" :options="$categorias" :value="$docente->categoria->value ?? ($docente->categoria ?? '')" />
                <x-formulario.seleccion :name="$n('tipo_contrato')" label="Tipo de Contrato *" :options="$contratos"
                    :value="$docente->tipo_contrato->value ?? ($docente->tipo_contrato ?? '')" />
                <x-formulario.entrada name="convenio" label="Convenio *" maxlength="45" :value="$docente->convenio ?? ''" />
                <x-formulario.entrada name="categoria" label="Categoria *" maxlength="45" :value="$docente->categoria ?? ''" />
                <x-formulario.entrada name="IBAN" label="IBAN *" maxlength="45" :value="$docente->IBAN ?? ''" />
            </div>
        </fieldset>

        <fieldset class="mb-8">
            <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Horas y Fechas
            </legend>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-formulario.entrada name="numero_horas" label="Numero de horas *" type="number" min="0"
                    step="0.01" :value="$docente->numero_horas ?? ''" />
                <x-formulario.entrada name="horas_presenciales" label="Horas presenciales" type="number" min="0"
                    step="0.01" :value="$docente->horas_presenciales ?? ''" />
                <x-formulario.entrada name="horas_virtuales" label="Horas virtuales" type="number" min="0"
                    step="0.01" :value="$docente->horas_virtuales ?? ''" />
                <x-formulario.entrada name="fecha_inicio" label="Fecha inicio *" type="date" :value="$fecha($curso->fecha_inicio ?? null)" />
                <x-formulario.entrada name="fecha_fin" label="Fecha fin *" type="date" :value="$fecha($curso->fecha_fin ?? null)" />
            </div>
        </fieldset>

        @unless ($incrustado)
            <div class="flex flex-wrap justify-end gap-3">
                @if (\Illuminate\Support\Facades\Route::has('docente.index'))
                    <a href="{{ route('docente.index') }}"
                        class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                        Cancelar
                    </a>
                @endif
                <button type="reset"
                    class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    Limpiar
                </button>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    {{ $editando ? 'Actualizar docente' : 'Guardar docente' }}
                </button>
            </div>
        </form>
    @endunless
</section>
