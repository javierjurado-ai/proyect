@php
    // Variables que usa este parcial (vienen del ámbito de la vista que lo incluye):
    // $alumno, $editando, $curso, $cursos, $personas, $empresas,
    // $jornadas, $categoriasProfesionales, $nivelesEstudio, $siNo
    // Se incluye así: @include('alumno.formulario', ['incrustado' => true])
    $incrustado = $incrustado ?? false;
    $editando = $editando ?? false;
    $alumno = $alumno ?? null;
@endphp

<section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
    @unless ($incrustado)
        <form id="alumno-form" method="POST"
            action="{{ $editando ? route('alumno.update', $alumno->id) : route('alumno.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif
        @endunless

        @if ($incrustado)
            <input type="hidden" name="curso_id" value="{{ $curso->id }}">
        @endif

        <fieldset class="mb-8">
            <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos de la matrícula
            </legend>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @unless ($incrustado)
                    <x-formulario.selector name="curso_id" label="Curso *" placeholder="Busca un curso..." :options="$cursos"
                        contenedor="md:col-span-2" :value="$alumno->curso_id ?? ''" />

                    <x-formulario.selector name="persona_id" label="Persona *" placeholder="Busca una persona..."
                        :options="$personas" contenedor="md:col-span-2" :value="$alumno->persona_id ?? ''" />
                @endunless

                <x-formulario.selector name="empresa_id" label="Empresa *" placeholder="Busca una empresa..."
                    :options="$empresas" contenedor="md:col-span-2" :value="$alumno->empresa_id ?? ''" />
            </div>
        </fieldset>

        <fieldset class="mb-8">
            <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Situación laboral</legend>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-formulario.seleccion name="jornada_laboral" label="Jornada laboral *" placeholder="-- Selecciona --"
                    :options="$lopd" :value="$alumno->jornada_laboral->value ?? ($alumno->jornada_laboral ?? '')" />
                <x-formulario.seleccion name="categoria_profesional" label="Categoría profesional *" 
                :options="$lopd" :value="$alumno->categoria_profesional->value ?? ($alumno->jornada_laboral ?? '')" />
                <x-formulario.seleccion name="erte" label="ERTE *" 
                :options="$lopd" :value="$alumno->erte->value ?? ($alumno->erte ?? '')" />
                <x-formulario.seleccion name="fijo_discontinuo" label="Fijo discontinuo *" 
                :options="$lopd" :value="$alumno->fijo_discontinuo->value ?? ($alumno->fijo_discontinuo ?? '')" />
            </div>
        </fieldset>

        <fieldset class="mb-8">
            <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Formación y certificados
            </legend>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-formulario.seleccion name="nivel_estudio" label="Nivel de estudios *" :options="$nivelesEstudio"
                    :value="$alumno->nivel_estudio ?? ''" />
                <x-formulario.seleccion name="diploma" label="Diploma *" 
                :options="$lopd" :value="$alumno->diploma->value ?? ($alumno->diploma ?? '')" />
                <x-formulario.seleccion name="certificado_discapacidad" label="Certificado de discapacidad *"
                    :options="$lopd" :value="$alumno->certificado_discapacidad->value ?? ($alumno->certificado_discapacidad ?? '')" />
                <x-formulario.seleccion name="certificado_afectado_terrorismo" label="Afectado por terrorismo *"
                    :options="$lopd" :value="$alumno->certificado_afectado_terrorismo->value ?? ($alumno->certificado_afectado_terrorismo ?? '')" />
                <x-formulario.seleccion name="certificado_afectado_violencia_genero" label="Afectado por violencia de género *" 
                :options="$lopd" :value="$alumno->certificado_afectado_violencia_genero->value ?? ($alumno->certificado_afectado_violencia_genero ?? '')" />
            </div>
        </fieldset>

        @unless ($incrustado)
            <div class="flex flex-wrap justify-end gap-3">
                @if (\Illuminate\Support\Facades\Route::has('alumno.index'))
                    <a href="{{ route('alumno.index') }}"
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
                    {{ $editando ? 'Actualizar alumno' : 'Guardar alumno' }}
                </button>
            </div>
        </form>
    @endunless
</section>
