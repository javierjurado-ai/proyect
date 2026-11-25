@php
    $editando = isset($curso);

    // Turnos para el desplegable: "MAÑANA" → "Mañana" (respetando la ñ)
    $opcionesTurno = collect($turnos)->mapWithKeys(
        fn ($texto, $valor) => [$valor => mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8')]
    );

    // Modalidades: "TELEFORMACION" → "Teleformación"
    $opcionesModalidad = collect($modalidades ?? [])->mapWithKeys(
        fn ($texto, $valor) => [$valor => $valor === 'TELEFORMACION' ? 'Teleformación' : mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8')]
    );

    $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('Y-m-d') : '';
    $hora  = fn ($h) => $h ? substr($h, 0, 5) : '';

    // Apartado "Calendario": no se guarda en la tabla curso, solo sirve para crear los días del calendario
    $nombresDias  = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $diasMarcados = array_map('intval', (array) old('dias_semana', [1, 2, 3, 4, 5]));
    $diasCalendario = $diasCalendario ?? 0;
    // Al editar, se ofrece desplegado si el curso aún no tiene días o si se envió marcado
    $generar = ! $editando || old('generar_calendario', $diasCalendario === 0 ? '1' : null);
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
                    {{-- codigo_curso ya no existe: el curso se identifica por acción + grupo --}}
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
                        name="nivel_formacion"
                        label="Nivel de formación"
                        type="number"
                        min="0"
                        max="3"
                        :value="$curso->nivel_formacion ?? 0" />
                    
                    <x-formulario.entrada
                        name="empresa_formadora"
                        label="Empresa formadora *"
                        maxlength="50"
                        :value="$curso->empresa_formadora ?? ''" />

                    <x-formulario.selector
                        name="formacion_id"
                        label="Formación"
                        placeholder="Busca una formación..."
                        :options="$formaciones"
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
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Modalidad y horas</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    {{-- Desplegable, igual que el turno --}}
                    <x-formulario.seleccion
                        name="modalidad"
                        label="Modalidad *"
                        placeholder="-- Elige una modalidad --"
                        :options="$opcionesModalidad"
                        :value="$curso->modalidad->value ?? ''" />

                    <x-formulario.entrada
                        name="horas_presenciales"
                        label="Horas presenciales"
                        type="number"
                        min="0"
                        step="0.01"
                        :value="$curso->horas_presenciales ?? ''" />

                    <x-formulario.entrada
                        name="horas_teleformacion"
                        label="Horas teleformación"
                        type="number"
                        min="0"
                        step="0.01"
                        :value="$curso->horas_teleformacion ?? ''" />
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

                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
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

                {{-- Solo para turno partido (se muestra u oculta según el turno elegido) --}}
                    <div id="segundo-tramo" @class(['hidden' => old('turno', $curso->turno->value ?? $curso->turno ?? '') !== 'PARTIDO'])>
                        <span class="mb-1 block text-sm font-medium text-slate-700">Segundo tramo (turno partido) *</span>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                            <x-formulario.entrada name="hora_inicio2" label="Hora inicio *" type="time" />
                            <x-formulario.entrada name="hora_final2" label="Hora fin *" type="time" />
                        </div>
            </fieldset>

            {{-- Calendario: genera los días en curso_calendario (no se guarda en la tabla curso) --}}
            <fieldset class="mb-8" id="apartado-calendario">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Calendario del curso</legend>

                @if ($editando)
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-stone-200 bg-stone-50 px-4 py-3 text-sm">
                        <span class="text-slate-600">
                            @if ($diasCalendario)
                                Este curso tiene <strong class="text-marca-950">{{ $diasCalendario }} {{ $diasCalendario === 1 ? 'día' : 'días' }}</strong> en su calendario.
                                Si cambias las fechas o el horario, esos días se ajustan solos.
                            @else
                                Este curso todavía no tiene días en su calendario.
                            @endif
                        </span>
                        <label class="inline-flex items-center gap-2 font-semibold text-marca-950">
                            <input type="hidden" name="generar_calendario" value="0">
                            <input type="checkbox" name="generar_calendario" value="1" id="generar_calendario"
                                class="h-4 w-4 rounded border-stone-300 accent-marca-800" @checked($generar)>
                            Añadir días al calendario
                        </label>
                    </div>
                @endif

                <div id="opciones-calendario" class="space-y-5">
                    <div>
                        <span class="mb-2 block text-sm font-medium text-slate-700">Días de clase *</span>

                        <input type="hidden" name="calendario_modo" value="semana">
                        <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Días de clase" id="dias-semana">
                            @foreach ($nombresDias as $numero => $nombreDia)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="dias_semana[]" value="{{ $numero }}" class="peer sr-only"
                                        @checked(in_array($numero, $diasMarcados, true))>
                                    <span title="{{ $nombreDia }}"
                                        class="inline-flex h-10 min-w-12 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-marca-700 peer-checked:border-marca-800 peer-checked:bg-marca-800 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-marca-700 peer-focus-visible:ring-offset-1">
                                        {{ \Illuminate\Support\Str::substr($nombreDia, 0, 3) }}
                                    </span>
                                </label>
                            @endforeach
                            <button type="button" data-dias="1,2,3,4,5"
                                class="ml-1 px-2 text-xs font-semibold text-marca-800 underline underline-offset-2 hover:text-marca-950">L–V</button>
                            <button type="button" data-dias="1,2,3,4,5,6,7"
                                class="px-2 text-xs font-semibold text-marca-800 underline underline-offset-2 hover:text-marca-950">Todos</button>
                        </div>
                        @error('dias_semana')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Solo para turno partido (se muestra u oculta según el turno elegido) --}}
                    <div id="segundo-tramo" @class(['hidden' => old('turno', $curso->turno->value ?? $curso->turno ?? '') !== 'PARTIDO'])>
                        <span class="mb-1 block text-sm font-medium text-slate-700">Segundo tramo (turno partido) *</span>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                            <x-formulario.entrada name="hora_inicio2" label="Hora inicio *" type="time" />
                            <x-formulario.entrada name="hora_final2" label="Hora fin *" type="time" />
                        </div>
                    </div>
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

                    {{-- El aula virtual va ahora en su propia tabla (curso_aula_virtual) --}}

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
                        rows="6"
                        :value="$curso->contenido ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Observaciones</legend>
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
        const horarios = { 'MAÑANA': ['09:00', '14:00'], 'TARDE': ['16:00', '20:00'], 'PARTIDO': ['09:00', '14:00', '16:00', '18:00'] };
        turno.addEventListener('change', () => {
            const h = horarios[turno.value];
            if (!h) return;
            if (!horaInicio.value) horaInicio.value = h[0];
            if (!horaFinal.value)  horaFinal.value  = h[1];
            // Turno partido: también propone el segundo tramo
            const ini2 = document.getElementById('hora_inicio2');
            const fin2 = document.getElementById('hora_final2');
            if (h[2] && !ini2.value) ini2.value = h[2];
            if (h[3] && !fin2.value) fin2.value = h[3];
        });

        // La fecha de fin no puede ser anterior a la de inicio
        const ajustarFechaFin = () => { fechaFin.min = fechaIni.value; };
        fechaIni.addEventListener('change', ajustarFechaFin);
        ajustarFechaFin();

        // La hora de fin no puede ser anterior a la de inicio (y el 2º tramo va después del 1º)
        const horaInicio2 = document.getElementById('hora_inicio2');
        const horaFinal2  = document.getElementById('hora_final2');
        const ajustarHoraFin = () => {
            horaFinal.min   = horaInicio.value;
            horaInicio2.min = horaFinal.value;
            horaFinal2.min  = horaInicio2.value;
        };
        [horaInicio, horaFinal, horaInicio2, horaFinal2].forEach(c => c.addEventListener('change', ajustarHoraFin));
        ajustarHoraFin();

        // Apartado Calendario: al editar solo se usa si se marca "Añadir días al calendario"
        const generar  = document.getElementById('generar_calendario');
        const opciones = document.getElementById('opciones-calendario');
        const diasSem  = document.querySelectorAll('input[name="dias_semana[]"]');
        const segundoTramo = document.getElementById('segundo-tramo');
        const ajustarCalendario = () => {
            const activo  = !generar || generar.checked;
            const partido = turno.value === 'PARTIDO';
            opciones.classList.toggle('hidden', !activo);
            opciones.querySelectorAll('input, button').forEach(c => c.disabled = !activo);

            // Segundo tramo: solo con turno partido, y entonces es obligatorio
            segundoTramo.classList.toggle('hidden', !partido);
            segundoTramo.querySelectorAll('input').forEach(c => {
                c.disabled = !activo || !partido;
                c.required = activo && partido;
            });
        };
        generar?.addEventListener('change', ajustarCalendario);
        turno.addEventListener('change', ajustarCalendario);
        ajustarCalendario();

        // Botones rápidos de días: L–V / Todos
        document.querySelectorAll('[data-dias]').forEach(boton => {
            boton.addEventListener('click', () => {
                const dias = boton.dataset.dias.split(',');
                diasSem.forEach(c => c.checked = dias.includes(c.value));
            });
        });

        // Si se generan días, al menos uno marcado
        form.addEventListener('submit', (e) => {
            const activo = !generar || generar.checked;
            if (activo && !document.querySelector('input[name="dias_semana[]"]:checked')) {
                e.preventDefault();
                alert('Marca al menos un día de clase.');
            }
        });

        // Contador de caracteres de los textarea (objetivos, contenido, observaciones)
        document.querySelectorAll('[data-contador]').forEach(span => {
            const campo = document.getElementById(span.dataset.contador);
            campo?.addEventListener('input', () => span.textContent = campo.value.length);
        });

        // Horas según modalidad: presencial → solo presenciales, teleformación → solo teleformación
        const modalidad = document.getElementById('modalidad');
        const horasPres = document.getElementById('horas_presenciales');
        const horasTele = document.getElementById('horas_teleformacion');
        const ajustarHoras = () => {
            const m = modalidad.value;
            horasPres.disabled = m === 'TELEFORMACION';
            horasTele.disabled = m === 'PRESENCIAL';
            horasPres.required = m === 'PRESENCIAL' || m === 'MIXTA';
            horasTele.required = m === 'TELEFORMACION' || m === 'MIXTA';
        };
        modalidad.addEventListener('change', ajustarHoras);
        ajustarHoras();

        // El mensaje de éxito desaparece al empezar a escribir otro curso
        form.addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>

</x-interfaz.layout.app>