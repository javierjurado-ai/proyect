@php
    // Obtenemos el ID del centro tanto si estamos editando ($aula->centro_id) como si estamos creando ($centro->id)
    $centroId = isset($aula) ? $aula->centro_id : $centro->id;
    $rutaVolver = $centroId ? url("/view/centro/{$centroId}") : route('aula.index');
@endphp

<x-interfaz.layout.app :titulo="isset($aula) ? 'Editar aula' : 'Nueva aula'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera :titulo="isset($aula) ? 'Editar aula' : 'Nueva aula'" subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            <a href="{{ $rutaVolver }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver al centro
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="formAula" method="POST" action="{{ isset($aula) ? route('aula.update', $aula->id) : route('aula.store', $centro->id) }}">
            @csrf
            @if(isset($aula))
                @method('PUT')
            @else
                <input type="hidden" name="centro_id" value="{{ $centro->id }}">
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos del aula</legend>
                <div class="grid gap-5 grid-cols-1 md:grid-cols-3">
                    <x-formulario.entrada name="nombre_aula" label="Nombre del aula *" maxlength="45" required
                        contenedor="md:col-span-2" :value="$aula->nombre_aula ?? ''" />
                    
                    <x-formulario.seleccion name="curso_id" label="Curso *" :options="$cursos"
                        :value="$aula->curso_id ?? ''"
                        placeholder="-- Selecciona un curso --" required />

                    <x-formulario.entrada name="capacidad" label="Capacidad (personas) *" type="number" min="1" step="1" required 
                        :value="$aula->capacidad ?? ''" />
                    
                    <x-formulario.entrada name="dimension" label="Dimensión (m²)" type="number" min="0" step="1" 
                        :value="$aula->dimension ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Estado y uso</legend>
                <div class="grid gap-5 grid-cols-1 md:grid-cols-2">
                    <x-formulario.seleccion name="estado" label="Estado *" :options="$estados" 
                        :value="isset($aula) ? $aula->estado?->value : ''" required />
                    
                    <x-formulario.seleccion name="uso_aula" label="Uso del aula *" :options="$usos" 
                        :value="isset($aula) ? $aula->uso_aula?->value : ''" required />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Observaciones</legend>
                <div class="grid gap-5 grid-cols-1">
                    <x-formulario.area name="observaciones" label="Observaciones" maxlength="450" rows="3"
                        :value="old('observaciones', $aula->observaciones ?? '')"/>                    
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ $rutaVolver }}"
                   class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                    Cancelar
                </a>
                <button type="reset"
                        class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    Limpiar
                </button>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    {{ isset($aula) ? 'Actualizar aula' : 'Guardar aula' }}
                </button>
            </div>
        </form>
    </section>

    <script>
        // Contador de caracteres de observaciones
        document.querySelectorAll('[data-contador]').forEach(span => {
            const campo = document.getElementById(span.dataset.contador);
            const actualizar = () => span.textContent = campo.value.length;
            campo.addEventListener('input', actualizar);
            document.getElementById('formAula').addEventListener('reset', () => setTimeout(actualizar));
        });
    </script>

</x-interfaz.layout.app>