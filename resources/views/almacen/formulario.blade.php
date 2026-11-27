@php
    $editando = isset($almacen);
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar almacén' : 'Nuevo almacén'" :rol="$rol" :usuario="$nombre">

    <x-interfaz.panel.cabecera
        :titulo="$editando ? 'Editar almacén' : 'Nuevo almacén'"
        subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            <a href="{{ route('almacen.index', $editando && $almacen->activo ? ['almacen_id' => $almacen->id] : []) }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver a almacenes
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form method="POST" action="{{ $editando ? route('almacen.update', $almacen->id) : route('almacen.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Almacén</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="nombre" label="Nombre *" maxlength="60" contenedor="md:col-span-2"
                        placeholder="Almacén central Madrid" :value="$almacen->nombre ?? ''" />

                    <x-formulario.seleccion name="centro_id" label="Centro" :options="$centros"
                        placeholder="-- Sin centro --" :value="$almacen->centro_id ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Dirección</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="direccion" label="Dirección *" maxlength="150" contenedor="md:col-span-3"
                        :value="$almacen->direccion ?? ''" />
                    <x-formulario.entrada name="municipio" label="Municipio *" maxlength="45" :value="$almacen->municipio ?? ''" />
                    <x-formulario.entrada name="provincia" label="Provincia *" maxlength="45" :value="$almacen->provincia ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Gestión</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.seleccion name="responsable_id" label="Responsable" :options="$responsables"
                        placeholder="-- Sin responsable --" :value="$almacen->responsable_id ?? ''" contenedor="md:col-span-2" />

                    <div class="flex flex-col justify-end pb-2">
                        {{-- Sin marcar no se envía nada: el hidden manda el 0 --}}
                        <input type="hidden" name="activo" value="0">
                        <x-formulario.checkbox name="activo" label="Almacén activo"
                            :checked="(bool) old('activo', $almacen->activo ?? true)" />
                        @error('activo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">El responsable ve este almacén por defecto al entrar en «Almacenes».</p>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('almacen.index') }}"
                   class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    {{ $editando ? 'Actualizar almacén' : 'Guardar almacén' }}
                </button>
            </div>
        </form>
    </section>

</x-interfaz.layout.app>
