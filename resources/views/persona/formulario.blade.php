@php
    $editando = isset($persona);

    $fecha = fn($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('Y-m-d') : '';

    $opcionesGenero = collect($generos)->mapWithKeys(
        fn($texto, $valor) => [$valor => mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8')],
    );
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar formación' : 'Nueva formación'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera :titulo="$editando ? 'Editar formación' : 'Nueva formación'" subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            @if (\Illuminate\Support\Facades\Route::has('formacion.index'))
                <a href="{{ $volver }}"
                    class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                    ← Volver
                </a>
            @endif
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Mensaje de éxito --}}
    @if (session('ok') || session('status'))
        <div id="success-alert" role="status"
            class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('ok') ?? session('status') }}
        </div>
    @endif

    {{-- Error de API --}}
    @error('api')
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $message }}
        </div>
    @enderror

    {{-- Resumen de errores --}}
    @if ($errors->any() && !$errors->has('api'))
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">
                Revisa los siguientes campos ({{ $errors->count() }}
                {{ $errors->count() === 1 ? 'error' : 'errores' }}):
            </p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->messages() as $campo => $mensajes)
                    @foreach ($mensajes as $mensaje)
                        <li><span class="font-mono text-xs">{{ $campo }}</span>: {{ $mensaje }}</li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="formacion-form" method="POST"
            action="{{ $editando ? route('persona.update', $persona->id) : route('persona.crearPersona') }}">
            @csrf
            <input type="hidden" name="volver" value="{{ $volver ?? '' }}">
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos de la persona
                </legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="nombre" label="Nombre *" maxlength="45" :value="$persona->nombre ?? ''" />

                    <x-formulario.entrada name="primer_apellido" label="Primer apellido *" maxlength="45"
                        :value="$persona->primer_apellido ?? ''" />

                    <x-formulario.entrada name="segundo_apellido" label="Segundo apellido" maxlength="45"
                        :value="$persona->segundo_apellido ?? ''" />

                    <x-formulario.entrada name="fecha_nacimiento" label="Fecha de nacimiento *" type="date"
                        :value="$fecha($persona->fecha_nacimiento ?? null)" />

                    <x-formulario.seleccion name="genero" label="Género *" placeholder="-- Elige género --"
                        :options="$opcionesGenero" :value="$persona->genero->value ?? ($persona->genero ?? '')" />

                    <x-formulario.entrada name="nacionalidad" label="Nacionalidad *" maxlength="45"
                        :value="$persona->nacionalidad ?? ''" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Información general
                </legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="telefono" label="Teléfono *" type="tel" maxlength="20"
                        :value="$persona->telefono ?? ''" />

                    <x-formulario.entrada name="email" label="Email *" type="email" maxlength="100"
                        contenedor="md:col-span-2" :value="$persona->email ?? ''" />

                    <x-formulario.entrada name="direccion" label="Dirección *" maxlength="150"
                        contenedor="md:col-span-3" :value="$persona->direccion ?? ''" />

                    <x-formulario.entrada name="DNI" label="DNI" maxlength="15" :value="$persona->DNI ?? ''" />

                    <x-formulario.entrada name="NIE" label="NIE" maxlength="15" :value="$persona->NIE ?? ''" />

                    <x-formulario.entrada name="numero_seguridad_social" label="Seguridad Social *" maxlength="20"
                        :value="$persona->numero_seguridad_social ?? ''" />

                    <x-formulario.seleccion name="LOPD" label="LOPD *" placeholder="-- Selecciona --"
                        :options="$lopd" :value="$persona->LOPD->value ?? ($persona->LOPD ?? '')" />
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                @if (\Illuminate\Support\Facades\Route::has('formacion.index'))
                    <a href="{{ $volver ?: route('formacion.index') }}"
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
        }, {
            once: true
        });
    </script>

</x-interfaz.layout.app>
