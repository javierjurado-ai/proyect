<x-interfaz.layout.app titulo="Nuevo contacto de centro" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Nuevo contacto de centro" subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            <a href="{{ route('centro.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver a centros
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="contacto-centro-form" method="POST" action="{{ route('centro_contactos.store') }}">
            @csrf

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Centro</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.seleccion name="centro_id" label="Centro *" :options="$centros"
                        placeholder="-- Selecciona un centro --" contenedor="md:col-span-2" />

                    <x-formulario.seleccion name="contacto" label="Tipo de contacto *" :options="$tiposContacto"
                        placeholder="-- Selecciona un tipo --" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos del contacto</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="nombre" label="Nombre *" maxlength="45" />
                    <x-formulario.entrada name="telefono_personal" label="Teléfono personal *" type="tel" maxlength="45" />
                    <x-formulario.entrada name="email_personal" label="Email personal *" type="email" maxlength="45" />
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('centro.index') }}"
                   class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                    Cancelar
                </a>
                <button type="reset"
                        class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    Limpiar
                </button>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    Guardar contacto
                </button>
            </div>
        </form>
    </section>

    <script>
        // El mensaje de éxito desaparece al empezar a escribir otro contacto
        document.getElementById('contacto-centro-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>

</x-interfaz.layout.app>