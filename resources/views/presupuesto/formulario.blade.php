@php
    $presupuesto = $presupuesto ?? null;
    $editando = (bool) $presupuesto;
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar presupuesto' : 'Nuevo presupuesto'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera
        :titulo="$editando ? 'Editar presupuesto' : 'Nuevo presupuesto'"
        subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            <a href="{{ route('presupuesto.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver a presupuestos
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="formPresupuesto" method="POST"
              action="{{ $editando ? route('presupuesto.update', $presupuesto->id) : route('presupuesto.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            {{-- El componente usa old() primero: si hay un error se conserva lo escrito --}}
            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Formación</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="nombre_formacion" label="Nombre de la formación *" maxlength="45"
                        contenedor="md:col-span-2" :value="$presupuesto->nombre_formacion ?? ''" />

                    <x-formulario.entrada name="horas_totales" label="Horas totales *" type="number" min="1" step="0.5"
                        :value="$presupuesto->horas_totales ?? ''" />

                    <x-formulario.entrada name="numero_alumnos" label="Número de alumnos *" type="number" min="1" step="1"
                        :value="$presupuesto->numero_alumnos ?? ''" />

                    <x-formulario.entrada name="ubicacion_formacion" label="Ubicación de la formación *" maxlength="90"
                        contenedor="md:col-span-2" :value="$presupuesto->ubicacion_formacion ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Bonificación</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="importe_bonificado" label="Importe bonificado (€) *" type="number"
                        min="0" max="99999999.99" step="0.01" placeholder="0.00"
                        :value="$presupuesto->importe_bonificado ?? ''" />

                    {{-- Al crear no puede ser pasada; al editar se puede dejar la que tenía --}}
                    <x-formulario.entrada name="fecha_bonificado" label="Fecha de bonificación *" type="date"
                        :min="$editando ? null : now()->toDateString()"
                        :value="$presupuesto?->fecha_bonificado?->format('Y-m-d') ?? ''" />
                </div>
            </fieldset>

            <div class="flex flex-wrap items-center justify-between gap-3">
                {{-- Eliminar solo al editar --}}
                @if ($editando)
                    <button type="button"
                            onclick="if (confirm('¿Seguro que quieres eliminar este presupuesto?')) document.getElementById('delete-form').submit();"
                            class="inline-flex items-center justify-center rounded-lg border border-red-300 px-5 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                        Eliminar
                    </button>
                @else
                    <span></span>
                @endif

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('presupuesto.index') }}"
                       class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                        Cancelar
                    </a>
                    <button type="reset"
                            class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                        Limpiar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                        {{ $editando ? 'Actualizar presupuesto' : 'Guardar presupuesto' }}
                    </button>
                </div>
            </div>
        </form>

        {{-- Formulario oculto para el DELETE --}}
        @if ($editando)
            <form id="delete-form" action="{{ route('presupuesto.destroy', $presupuesto->id) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </section>

    <script>
        // El mensaje de éxito desaparece al empezar a escribir
        document.getElementById('formPresupuesto').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>

</x-interfaz.layout.app>