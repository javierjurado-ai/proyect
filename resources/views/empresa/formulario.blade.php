@php
    $editando = isset($empresa);
    $rolObj = $editando ? $empresa->roles->first()?->rol_empresa : null;
    // Si viene como Enum, extraemos su string con ->value; si es string directo, lo usamos
    $rolActual = $rolObj instanceof \BackedEnum ? $rolObj->value : ($rolObj ?? '');
    $rolSeleccionado = old('roles.0', $rolActual);
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
                        name="direccion"
                        label="Dirección"
                        maxlength="150"
                        contenedor="md:col-span-2"
                        :value="$empresa->direccion ?? ''" />

                    <x-formulario.entrada
                        name="codigo_postal"
                        label="Código postal"
                        maxlength="5"
                        :value="$empresa->codigo_postal ?? ''" />

                    <x-formulario.entrada
                        name="municipio"
                        label="Municipio"
                        maxlength="45"
                        :value="$empresa->municipio ?? ''" />

                    <x-formulario.entrada
                        name="provincia"
                        label="Provincia"
                        maxlength="45"
                        :value="$empresa->provincia ?? ''" />

                    <x-formulario.entrada
                        name="email_facturacion"
                        label="Email"
                        type="email"
                        maxlength="100"
                        :value="$empresa->email_facturacion ?? ''" />

                    <x-formulario.entrada
                        name="telefono"
                        label="Teléfono"
                        maxlength="20"
                        :value="$empresa->telefono ?? ''" />

                    <x-formulario.entrada
                        name="total_trabajadores"
                        label="Total trabajadores"
                        type="number"
                        min="0"
                        step="1"
                        placeholder="0"
                        :value="$empresa->total_trabajadores ?? ''" />

                    <div>
                        <label for="roles" class="mb-2 block text-xs font-medium text-slate-1100">Tipo de Empresa</label>
                        <select id="roles" name="roles[]"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-marca-700">
                            <option value="">Selecciona un rol...</option>
                            @foreach (\App\Models\Enums\RolEmpresa::cases() as $rolEnum)
                                <option value="{{ $rolEnum->value }}" @selected($rolSeleccionado === $rolEnum->value)>
                                    {{ ucfirst(strtolower($rolEnum->value)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('roles')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        @error('roles.*')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            @php
                // Tipos marcados: lo enviado si hubo error, si no los que ya tiene (de los que puede ver)
                $marcados = old('roles', $editando
                    ? $empresa->roles->pluck('rol_empresa')->filter()->map(fn ($r) => $r->value)->intersect(array_keys($tipos))->values()->all()
                    : (count($tipos) === 1 ? array_keys($tipos) : []));
            @endphp
            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Tipo de empresa *</legend>
                <div class="flex flex-wrap gap-6">
                    @foreach ($tipos as $valor => $texto)
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="roles[]" value="{{ $valor }}" @checked(in_array($valor, $marcados, true))
                                class="h-4 w-4 rounded border-slate-300 text-marca-800 focus:ring-marca-800">
                            {{ $texto }}
                        </label>
                    @endforeach
                </div>
                @error('roles')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('roles.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($editando && ($otros = $empresa->roles->pluck('rol_empresa')->filter()->map(fn ($r) => $r->value)->diff(array_keys($tipos)))->isNotEmpty())
                    <p class="mt-2 text-xs text-slate-500">
                        Además es {{ mb_strtolower($otros->map(fn ($t) => ucfirst(strtolower($t)))->implode(' y ')) }}; eso no se cambia desde aquí.
                    </p>
                @endif
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
        const cif = document.getElementById('cif');
        document.getElementById('empresa-form').addEventListener('submit', () => {
            cif.value = cif.value.replace(/\s+/g, '').toUpperCase();
        });

        document.getElementById('empresa-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>

</x-interfaz.layout.app>