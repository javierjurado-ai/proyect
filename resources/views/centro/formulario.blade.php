@php
    $editando = isset($centro);
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar centro' : 'Nuevo centro'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera
        :titulo="$editando ? 'Editar centro' : 'Nuevo centro'"
        subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            @if (\Illuminate\Support\Facades\Route::has('centro.index'))
                <a href="{{ route('centro.index') }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    ← Volver a centros
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
        <form id="centro-form" method="POST"
              action="{{ $editando ? route('centro.update', $centro->id) : route('centro.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos del centro</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="nombre"
                        label="Nombre *"
                        maxlength="45"
                        contenedor="md:col-span-2"
                        :value="$centro->nombre ?? ''" />

                    <x-formulario.seleccion
                        name="tipo_centro"
                        label="Tipo de centro *"
                        :options="$tiposCentro"
                        :value="$centro->tipo_centro->value ?? ''" />

                    <x-formulario.selector
                        name="empresa_id"
                        label="Empresa (obligatoria si es externo)"
                        placeholder="Busca una empresa..."
                        :options="$empresas"
                        contenedor="md:col-span-2"
                        :value="$centro->empresa_id ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Ubicación</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="direccion"
                        label="Dirección *"
                        maxlength="45"
                        contenedor="md:col-span-3"
                        :value="$centro->direccion ?? ''" />

                    <x-formulario.entrada
                        name="municipio"
                        label="Municipio *"
                        maxlength="45"
                        :value="$centro->municipio ?? ''" />

                    <x-formulario.entrada
                        name="provincia"
                        label="Provincia *"
                        maxlength="45"
                        :value="$centro->provincia ?? ''" />

                    <x-formulario.entrada
                        name="codigo_postal"
                        label="Código postal *"
                        maxlength="5"
                        inputmode="numeric"
                        pattern="[0-9]{5}"
                        placeholder="28001"
                        :value="$centro->codigo_postal ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Contacto</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada
                        name="telefono"
                        label="Teléfono *"
                        type="tel"
                        maxlength="45"
                        :value="$centro->telefono ?? ''" />

                    <x-formulario.entrada
                        name="email"
                        label="Email"
                        type="email"
                        maxlength="45"
                        :value="$centro->email ?? ''" />
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                @if (\Illuminate\Support\Facades\Route::has('centro.index'))
                    <a href="{{ route('centro.index') }}"
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
                    {{ $editando ? 'Actualizar centro' : 'Guardar centro' }}
                </button>
            </div>
        </form>
    </section>

    <script>
        // El mensaje de éxito desaparece al empezar a escribir
        document.getElementById('centro-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>

</x-interfaz.layout.app>