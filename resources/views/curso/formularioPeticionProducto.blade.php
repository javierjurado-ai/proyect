@php
    $editando = $editando ?? false;
    $peticion = $peticion ?? null;

    $fecha = fn($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('Y-m-d') : '';

    $curso = $curso ?? null;
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar petición' : 'Nueva petición'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera :titulo="$editando ? 'Editar petición logística' : 'Nueva petición logística'" subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            <a href="{{ route('logistica.index') }}"
                class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }}
            {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="logistica-form" method="POST"
            action="{{ $editando ? route('logistica.update', $peticion->id) : route('logistica.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos de la petición
                </legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    @if ($curso)
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Curso *</label>
                            <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                            <input type="text" value="{{ $cursos[$curso->id] ?? '' }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-stone-300 bg-stone-100 px-3 py-2 text-sm text-slate-600">
                        </div>
                    @else
                        <x-formulario.selector name="curso_id" label="Curso *" placeholder="Busca un curso..."
                            :options="$cursos" :value="$peticion->curso_id ?? request('curso_id', '')" />
                    @endif

                    <x-formulario.selector name="solicitante_id" label="Solicitante *"
                        placeholder="Busca una persona..." :options="$personas" :value="$peticion->solicitante_id ?? ''" />

                    <x-formulario.seleccion name="estado" label="Estado *" placeholder="-- Elige un estado --"
                        :options="$estados" :value="$peticion->estado->value ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Origen y destino
                </legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.selector name="almacen_id" label="Almacén *" placeholder="Busca un almacén..."
                        :options="$almacenes" :value="$peticion->almacen_id ?? ''" />

                    <x-formulario.selector name="centro_id" label="Centro destino *" placeholder="Busca un centro..."
                        :options="$centros" :value="$peticion->centro_id ?? ''" />

                    <x-formulario.entrada name="direccion_envio" label="Dirección de envío" maxlength="255"
                        :value="$peticion->direccion_envio ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Fechas</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="fecha" label="Fecha de la petición *" type="date"
                        :value="$fecha($peticion->fecha ?? now())" />

                    <x-formulario.entrada name="fecha_necesaria" label="Fecha necesaria *" type="date"
                        :value="$fecha($peticion->fecha_necesaria ?? null)" />

                    <x-formulario.entrada name="fecha_devolucion_prevista" label="Devolución prevista" type="date"
                        :value="$fecha($peticion->fecha_devolucion_prevista ?? null)" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Envío</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="transportista" label="Transportista" maxlength="100"
                        :value="$peticion->transportista ?? ''" />

                    <x-formulario.entrada name="seguimiento" label="Nº de seguimiento" maxlength="100"
                        :value="$peticion->seguimiento ?? ''" />

                    <x-formulario.entrada name="coste_envio" label="Coste de envío (€)" type="number" min="0"
                        step="0.01" :value="$peticion->coste_envio ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Observaciones</legend>
                <x-formulario.area name="observaciones" label="Observaciones" maxlength="500" rows="3"
                    :value="$peticion->observaciones ?? ''" />
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('logistica.index') }}"
                    class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                    Cancelar
                </a>
                <button type="reset"
                    class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    Limpiar
                </button>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    {{ $editando ? 'Actualizar petición' : 'Guardar petición' }}
                </button>
            </div>
        </form>
    </section>

    <script>
        // La devolución no puede ser anterior a la fecha necesaria
        const necesaria = document.getElementById('fecha_necesaria');
        const devolucion = document.getElementById('fecha_devolucion_prevista');
        const ajustar = () => {
            devolucion.min = necesaria.value;
        };
        necesaria.addEventListener('change', ajustar);
        ajustar();
    </script>

</x-interfaz.layout.app>
