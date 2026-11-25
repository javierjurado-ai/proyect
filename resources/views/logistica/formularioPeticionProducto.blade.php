@php
    $editando = $editando ?? false;
    $peticion = $peticion ?? null;

    $fecha = fn($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('Y-m-d') : '';

    $curso = $curso ?? null;
    $direccion = $peticion->direccion_envio ?? ($curso?->centro?->direccion ?? '');
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
         action="{{ route('curso.logistica.create', $curso) }}">
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
                </div>
            </fieldset>


            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Información del
                    producto</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="producto" label="Producto *" maxlength="45" :value="$peticion?->peticionProductos->first()?->producto ?? ''" />
                    <x-formulario.entrada name="cantidad" label="Cantidad *" type="number" min="0"
                        max="99" :value="$peticion?->peticionProductos->first()?->cantidad ?? 0" />
                    @if ($curso && $direccion)
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Dirección de envío *</label>
                            <input type="hidden" name="direccion_envio" value="{{ $direccion }}">
                            <input type="text" value="{{ $direccion }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-stone-300 bg-stone-100 px-3 py-2 text-sm text-slate-600">
                        </div>
                    @else
                        <x-formulario.entrada name="direccion_envio" label="Dirección de envío *" maxlength="255"
                            :value="$direccion" />
                    @endif
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Fechas</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="fecha_necesaria" label="Fecha necesaria" type="date"
                        :value="$peticion?->fecha_necesaria?->format('Y-m-d') ?? ''" />
                    <x-formulario.entrada name="fecha_devolucion_prevista" label="Devolución prevista" type="date"
                        :value="$peticion?->fecha_devolucion_prevista?->format('Y-m-d') ?? ''" />
                    <x-formulario.area name="observaciones" label="Observaciones" maxlength="200" rows="3"
                        :value="$peticion->observaciones ?? ''" />
                </div>
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
