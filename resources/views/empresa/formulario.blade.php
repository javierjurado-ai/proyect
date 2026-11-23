@php
    $editando = isset($empresa);
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar empresa' : 'Nueva empresa'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera
        :titulo="$editando ? 'Editar empresa' : 'Nueva empresa'"
        subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            <a href="{{ route('empresa.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver a empresas
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Resumen de errores (igual que en cursos, centros y formaciones) --}}
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="empresa-form" method="POST"
              action="{{ $editando ? route('empresa.actualizar', $empresa->id) : route('empresa.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos de la empresa</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    {{-- El componente ya usa old() primero: si hay un error se conserva lo escrito --}}
                    <x-formulario.entrada
                        name="cif"
                        label="CIF *"
                        placeholder="B12345678"
                        maxlength="15"
                        class="uppercase"
                        :value="$empresa->cif ?? ''" />

                    <x-formulario.entrada
                        name="nombre"
                        label="Nombre *"
                        maxlength="100"
                        contenedor="md:col-span-2"
                        :value="$empresa->nombre ?? ''" />

                    <x-formulario.entrada
                        name="total_trabajadores"
                        label="Total trabajadores"
                        type="number"
                        min="0"
                        step="1"
                        placeholder="0"
                        :value="$empresa->total_trabajadores ?? ''" />
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('empresa.index') }}"
                   class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                    Cancelar
                </a>
                <button type="reset"
                        class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    Limpiar
                </button>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    {{ $editando ? 'Actualizar empresa' : 'Guardar empresa' }}
                </button>
            </div>
        </form>
    </section>

    <script>
        // El CIF se guarda en mayúsculas y sin espacios
        const cif = document.getElementById('cif');
        document.getElementById('empresa-form').addEventListener('submit', () => {
            cif.value = cif.value.replace(/\s+/g, '').toUpperCase();
        });

        // El mensaje de éxito desaparece al empezar a escribir
        document.getElementById('empresa-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>

</x-interfaz.layout.app>