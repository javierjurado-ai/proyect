@php
    $editando = isset($dia);

    $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('Y-m-d') : '';
    $hora  = fn ($h) => $h ? substr($h, 0, 5) : '';
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar día' : 'Nuevo día'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera
        :titulo="$editando ? 'Editar día' : 'Nuevo día'"
        subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            <a 
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver al curso
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="calendario-form" method="POST"
              action="{{ $editando ? route('curso.calendario.update', [$curso, $dia]) : route('curso.calendario.store', $curso) }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Día</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="fecha"
                        label="Fecha *"
                        type="date"
                        :value="$fecha($dia->fecha ?? request('fecha'))" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Primer tramo</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="hora_inicio1"
                        label="Hora inicio *"
                        type="time"
                        :value="$hora($dia->hora_inicio1 ?? null)" />

                    <x-formulario.entrada
                        name="hora_fin1"
                        label="Hora fin *"
                        type="time"
                        :value="$hora($dia->hora_fin1 ?? null)" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Segundo tramo (opcional)</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="hora_inicio2"
                        label="Hora inicio"
                        type="time"
                        :value="$hora($dia->hora_inicio2 ?? null)" />

                    <x-formulario.entrada
                        name="hora_fin2"
                        label="Hora fin"
                        type="time"
                        :value="$hora($dia->hora_fin2 ?? null)" />
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                <a
                   class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                    Cancelar
                </a>
                <button type="reset"
                        class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    Limpiar
                </button>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    {{ $editando ? 'Actualizar día' : 'Guardar día' }}
                </button>
            </div>
        </form>
    </section>

    <script>
        const horaInicio1 = document.getElementById('hora_inicio1');
        const horaFin1    = document.getElementById('hora_fin1');
        const horaInicio2 = document.getElementById('hora_inicio2');
        const horaFin2    = document.getElementById('hora_fin2');

        // Cada hora de fin no puede ser anterior a su hora de inicio
        const ajustar = () => {
            horaFin1.min = horaInicio1.value;
            horaInicio2.min = horaFin1.value;
            horaFin2.min = horaInicio2.value;
        };
        [horaInicio1, horaFin1, horaInicio2].forEach(c => c.addEventListener('change', ajustar));
        ajustar();
    </script>

</x-interfaz.layout.app>