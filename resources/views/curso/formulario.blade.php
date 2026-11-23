@php
    $editando = isset($curso);

    // Turnos para el desplegable: "MAÑANA" → "Mañana" (respetando la ñ)
    $opcionesTurno = collect($turnos)->mapWithKeys(
        fn ($texto, $valor) => [$valor => mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8')]
    );

    $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('Y-m-d') : '';
    $hora  = fn ($h) => $h ? substr($h, 0, 5) : '';
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar curso' : 'Nuevo curso'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera
        :titulo="$editando ? 'Editar curso' : 'Nuevo curso'"
        subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            @if (\Illuminate\Support\Facades\Route::has('curso.crearTablaCurso'))
                <a href="{{ route('curso.crearTablaCurso') }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    ← Volver a cursos
                </a>
            @endif
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Mensaje de éxito --}}
    @if (session('ok'))
        <div id="success-alert" role="status"
             class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    {{-- Resumen de errores --}}
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="curso-form" method="POST"
              action="{{ $editando ? route('curso.update', $curso->id) : route('curso.agregarCurso') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos del curso</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="codigo_curso"
                        label="Código *"
                        type="number"
                        min="1"
                        :value="$curso->codigo_curso ?? ''" />

                    <x-formulario.entrada
                        name="codigo_accion"
                        label="Código de acción *"
                        maxlength="45"
                        :value="$curso->codigo_accion ?? ''" />

                    <x-formulario.entrada
                        name="codigo_grupo"
                        label="Código de grupo *"
                        maxlength="45"
                        :value="$curso->codigo_grupo ?? ''" />

                    <x-formulario.entrada
                        name="empresa_formadora"
                        label="Empresa formadora *"
                        maxlength="100"
                        contenedor="md:col-span-2"
                        :value="$curso->empresa_formadora ?? ''" />

                    <x-formulario.selector
                        name="formacion_id"
                        label="Formación *"
                        placeholder="Busca una formación..."
                        :options="$formaciones"
                        contenedor="md:col-span-2"
                        :value="$curso->formacion_id ?? ''" />

                    <x-formulario.selector
                        name="centro_id"
                        label="Centro"
                        placeholder="Busca un centro..."
                        :options="$centros"
                        :value="$curso->centro_id ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Fechas y horario</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="fecha_inicio"
                        label="Fecha inicio *"
                        type="date"
                        :value="$fecha($curso->fecha_inicio ?? null)" />

                    <x-formulario.entrada
                        name="fecha_fin"
                        label="Fecha fin *"
                        type="date"
                        :value="$fecha($curso->fecha_fin ?? null)" />

                    {{-- Turno: desplegable --}}
                    <x-formulario.seleccion
                        name="turno"
                        label="Turno *"
                        placeholder="-- Elige un turno --"
                        :options="$opcionesTurno"
                        :value="$curso->turno->value ?? $curso->turno ?? ''" />

                    <x-formulario.entrada
                        name="hora_inicio"
                        label="Hora inicio *"
                        type="time"
                        :value="$hora($curso->hora_inicio ?? null)" />

                    <x-formulario.entrada
                        name="hora_final"
                        label="Hora fin *"
                        type="time"
                        :value="$hora($curso->hora_final ?? null)" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Aula y contacto</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="aula"
                        label="Aula *"
                        maxlength="45"
                        placeholder="Ej.: Aula 3"
                        :value="$curso->aula ?? ''" />

                    <x-formulario.entrada
                        name="aula_virtual"
                        label="Aula virtual *"
                        maxlength="45"
                        placeholder="Enlace o nombre de la sala"
                        :value="$curso->aula_virtual ?? ''" />

                    <x-formulario.entrada
                        name="tecnico_comunidad"
                        label="Técnico Comunidad *"
                        maxlength="45"
                        :value="$curso->tecnico_comunidad ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Contenidos y Objetivos</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 mb-5">
                    <x-formulario.area
                        name="objetivos"
                        label="Objetivos *"
                        maxlength="500"
                        rows="3"
                        :value="$curso->objetivos ?? ''" />

                    <x-formulario.area
                        name="contenido"
                        label="Contenido *"
                        maxlength="1000"
                        rows="3"
                        :value="$curso->contenido ?? ''" />
                </div>
                
                <x-formulario.area
                    name="observaciones"
                    label="Observaciones"
                    maxlength="200"
                    rows="3"
                    :value="$curso->observaciones ?? ''" />
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                @if (\Illuminate\Support\Facades\Route::has('curso.crearTablaCurso'))
                    <a href="{{ route('curso.crearTablaCurso') }}"
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
                    {{ $editando ? 'Actualizar curso' : 'Guardar curso' }}
                </button>
            </div>
        </form>
    </section>

    <script>
        const form       = document.getElementById('curso-form');
        const turno      = document.getElementById('turno');
        const horaInicio = document.getElementById('hora_inicio');
        const horaFinal  = document.getElementById('hora_final');
        const fechaIni   = document.getElementById('fecha_inicio');
        const fechaFin   = document.getElementById('fecha_fin');

        // Al elegir turno, propone un horario si las horas están vacías
        const horarios = { 'MAÑANA': ['09:00', '14:00'], 'TARDE': ['16:00', '20:00'] };
        turno.addEventListener('change', () => {
            const h = horarios[turno.value];
            if (!h) return;
            if (!horaInicio.value) horaInicio.value = h[0];
            if (!horaFinal.value)  horaFinal.value  = h[1];
        });

        // La fecha de fin no puede ser anterior a la de inicio
        const ajustarFechaFin = () => { fechaFin.min = fechaIni.value; };
        fechaIni.addEventListener('change', ajustarFechaFin);
        ajustarFechaFin();

        // La hora de fin no puede ser anterior a la de inicio
        const ajustarHoraFin = () => { horaFinal.min = horaInicio.value; };
        horaInicio.addEventListener('change', ajustarHoraFin);
        ajustarHoraFin();

        // Contador de caracteres de observaciones
        const obs = document.getElementById('observaciones');
        const contador = document.querySelector('[data-contador="observaciones"]');
        obs?.addEventListener('input', () => { if (contador) contador.textContent = obs.value.length; });

        // El mensaje de éxito desaparece al empezar a escribir otro curso
        form.addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>

</x-interfaz.layout.app>