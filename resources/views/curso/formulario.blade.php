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

    $opcionesNivel = [
        0 => 'Básico',
        1 => 'Superior',
    ];
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

                    {{-- CENTRO arriba --}}
                    <x-formulario.selector
                        name="centro_id"
                        label="Centro"
                        placeholder="Busca un centro..."
                        :options="$centros"
                        :value="$curso->centro_id ?? ''" />
                    
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

                    <x-formulario.seleccion
                        name="aula"
                        id="aula-select"
                        label="Aula *"
                        placeholder="-- Elige un aula --"
                        :options="[]"
                        :value="$curso->aula ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Modalidad y horas</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
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

             {{-- Bloque donde antes estaba Aula, ahora ponemos CONTACTO y NIVEL DE FORMACIÓN --}}
            <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-2">                
                <fieldset>
                    <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Nivel de formación</legend>
                    <x-formulario.seleccion
                        name="nivel_formacion"
                        label="Nivel de formación *"
                        placeholder="-- Elige un nivel --"
                        :options="$opcionesNivel"
                        :value="isset($curso) ? $curso->nivel_formacion : ''" />
                </fieldset>

                <fieldset>
                    <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Contacto</legend>
                    <x-formulario.entrada
                        name="tecnico_comunidad"
                        id="tecnico_comunidad"
                        label="Técnico Comunidad *"
                        maxlength="45"
                        :value="$curso->tecnico_comunidad ?? ''" />
                </fieldset>
            </div>
            
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



    // -------------------------------------------------------------
    // Lógica robusta para el Desplegable de Aula y Centro
    // -------------------------------------------------------------
    const todasLasAulas = @json($aulas ?? []);
    const selectAula = document.getElementById('aula-select');
    
    // Valores actuales del curso en modo edición (desde PHP)
    const aulaActual = @json($curso->aula ?? '');
    const centroIdActual = @json($curso->centro_id ?? '');

    const obtenerCentroId = () => {
        // Buscamos cualquier input o select del centro en el DOM
        const elCentro = document.querySelector('select[name="centro_id"], [name="centro_id"], select[id*="centro"], input[name="centro_id"]');
        if (elCentro && elCentro.value) {
            return elCentro.value;
        }
        return centroIdActual; // Respaldo con el ID de la base de datos
    };

    const actualizarAulasYModalidad = () => {
        const m = modalidad ? modalidad.value : '';
        const esTeleformacion = (m === 'TELEFORMACION');

        if (!selectAula) return;

        // Si es teleformación, bloqueamos el aula
        if (esTeleformacion) {
            selectAula.innerHTML = '<option value="">-- No aplica (Teleformación) --</option>';
            selectAula.value = '';
            selectAula.disabled = true;
            return;
        }

        const centroId = obtenerCentroId();

        selectAula.disabled = false;
        selectAula.innerHTML = '<option value="">-- Elige un aula --</option>';

        if (!centroId) {
            selectAula.innerHTML = '<option value="">-- Primero selecciona un centro --</option>';
            selectAula.disabled = true;
            return;
        }

        // Filtramos las aulas que coinciden con el centro_id
        const aulasFiltradas = todasLasAulas.filter(aula => String(aula.centro_id) === String(centroId));

        if (aulasFiltradas.length === 0) {
            selectAula.innerHTML = '<option value="">-- No hay aulas registradas en este centro --</option>';
            selectAula.disabled = true;
            return;
        }

        // Rellenamos el desplegable y seleccionamos la que tenía el curso
        aulasFiltradas.forEach(aula => {
            const option = document.createElement('option');
            option.value = aula.nombre_aula; 
            option.textContent = aula.nombre_aula;
            
            if (String(aula.nombre_aula).trim() === String(aulaActual).trim()) {
                option.selected = true;
            }
            
            selectAula.appendChild(option);
        });
    };

    // Escuchamos cambios en el centro
    const registrarEventosCentro = () => {
        const elCentroInput = document.querySelector('select[name="centro_id"], [name="centro_id"], select[id*="centro"], input[name="centro_id"]');
        if (elCentroInput) {
            ['change', 'input', 'blur'].forEach(evento => {
                elCentroInput.addEventListener(evento, actualizarAulasYModalidad);
            });
        }
    };

    if (modalidad) {
        modalidad.addEventListener('change', actualizarAulasYModalidad);
    }

    // Ejecución inicial y reintentos para asegurar que el componente de Laravel termine de pintar el DOM en Edición
    registrarEventosCentro();
    actualizarAulasYModalidad();
    setTimeout(actualizarAulasYModalidad, 200);
    setTimeout(actualizarAulasYModalidad, 600);  // Pequeño retardo para asegurar que el DOM del componente esté listo   


    // El mensaje de éxito desaparece al empezar a escribir
    form?.addEventListener('focusin', () => {
        document.getElementById('success-alert')?.remove();
    }, { once: true }); 
    </script>

</x-interfaz.layout.app>