@php
    $editando = isset($formacion);
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar formación' : 'Nueva formación'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera
        :titulo="$editando ? 'Editar formación' : 'Nueva formación'"
        subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            @if (\Illuminate\Support\Facades\Route::has('formacion.index'))
                <a href="{{ route('formacion.index') }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    ← Volver a formaciones
                </a>
            @endif
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>


    {{-- Error de API --}}
    @error('api')
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $message }}
        </div>
    @enderror

    {{-- Resumen de errores --}}
    @if ($errors->any() && ! $errors->has('api'))
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="formacion-form" method="POST"
              action="{{ $editando ? route('formacion.update', $formacion->id) : route('formacion.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos de la formación</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="nombre"
                        label="Nombre *"
                        maxlength="100"
                        contenedor="md:col-span-2"
                        :value="$formacion->nombre ?? ''" />

                    <x-formulario.entrada
                        name="codigo_accion"
                        label="Código acción"
                        type="number"
                        min="1"
                        :value="$formacion->codigo_accion ?? ''" />

                    {{-- 'sector' y 'modalidad' ya no son de formación (la modalidad va en cada curso) --}}
                    <x-formulario.entrada
                        name="horas_totales"
                        label="Horas totales"
                        type="number"
                        min="1"
                        step="0.5"
                        :value="$formacion->horas_totales ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Estado</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.checkbox
                        name="activo"
                        label="Formación activa"
                        :checked="old('activo', $editando ? $formacion->activo : true)" />
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                @if (\Illuminate\Support\Facades\Route::has('formacion.index'))
                    <a href="{{ route('formacion.index') }}"
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
                    {{ $editando ? 'Actualizar formación' : 'Guardar formación' }}
                </button>
            </div>
        </form>
    </section>

    <script>
        // El mensaje de éxito desaparece al empezar a escribir
        document.getElementById('formacion-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>

</x-interfaz.layout.app>