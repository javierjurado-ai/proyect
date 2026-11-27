@php
    $editando = isset($articulo);
    $valor = fn ($campo, $defecto = '') => $editando ? ($articulo->$campo instanceof \BackedEnum ? $articulo->$campo->value : $articulo->$campo) : $defecto;
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar artículo' : 'Nuevo artículo'" :rol="$rol" :usuario="$nombre">

    <x-interfaz.panel.cabecera
        :titulo="$editando ? 'Editar artículo' : 'Nuevo artículo'"
        subtitulo="Los campos marcados con * son obligatorios. El stock no se escribe aquí: entra registrando una compra.">
        <x-slot:acciones>
            <a href="{{ $editando ? route('articulo.visualizar', $articulo->id) : route('articulo.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="articulo-form" method="POST" action="{{ $editando ? route('articulo.update', $articulo->id) : route('articulo.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Artículo</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="codigo" label="Código *" maxlength="20" placeholder="EQ-PORT" class="uppercase"
                        :value="$valor('codigo')" />
                    <x-formulario.entrada name="nombre" label="Nombre *" maxlength="100" contenedor="md:col-span-2"
                        :value="$valor('nombre')" />

                    <x-formulario.seleccion name="tipo" label="Tipo *" :options="$tipos" :value="$valor('tipo')" />
                    <x-formulario.seleccion name="unidad" label="Se cuenta por *" :options="$unidades" placeholder="" :value="$valor('unidad', 'UNIDAD')" />
                    <x-formulario.seleccion name="proveedor_habitual_id" label="Proveedor habitual" :options="$proveedores"
                        placeholder="-- Ninguno --" :value="$valor('proveedor_habitual_id')" />

                    <x-formulario.area name="descripcion" label="Descripción" maxlength="300" contenedor="md:col-span-3"
                        :value="$valor('descripcion')" />
                </div>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Control en almacén</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.seleccion name="retornable" label="¿Vuelve al acabar el curso? *" :options="$siNo" placeholder="" :value="$valor('retornable', 'SI')" />
                    <x-formulario.seleccion name="controla_serie" label="¿Lleva nº de serie? *" :options="$siNo" placeholder="" :value="$valor('controla_serie', 'NO')" />
                    <x-formulario.entrada name="stock_minimo" label="Stock mínimo *" type="number" min="0" step="1"
                        :value="$valor('stock_minimo', 0)" />
                </div>
                <p class="mt-3 text-xs text-slate-500">
                    Con nº de serie, cada unidad se sigue por separado (portátiles, proyectores…). Solo se puede cambiar mientras el artículo no tenga stock.
                    Si el stock total baja del mínimo, sale como «reponer».
                </p>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Precios</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-formulario.entrada name="precio_coste" label="Precio de coste (€) *" type="number" min="0" step="0.01"
                        :value="$valor('precio_coste', '0.00')" />
                    <x-formulario.entrada name="precio_venta" label="Precio de venta (€)" type="number" min="0" step="0.01"
                        :value="$valor('precio_venta')" />
                    <x-formulario.entrada name="tipo_iva" label="IVA (%) *" type="number" min="0" max="100" step="0.01"
                        :value="$valor('tipo_iva', '21.00')" />
                </div>
            </fieldset>

            <div class="mb-8">
                <input type="hidden" name="activo" value="0">
                <x-formulario.checkbox name="activo" label="Artículo activo (si no, deja de salir al pedir material)"
                    :checked="(bool) old('activo', $editando ? $articulo->activo : true)" />
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ $editando ? route('articulo.visualizar', $articulo->id) : route('articulo.index') }}"
                   class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    {{ $editando ? 'Actualizar artículo' : 'Guardar artículo' }}
                </button>
            </div>
        </form>
    </section>

    <script>
        // El código se guarda en mayúsculas y sin espacios
        document.getElementById('articulo-form').addEventListener('submit', () => {
            const codigo = document.getElementById('codigo');
            codigo.value = codigo.value.replace(/\s+/g, '').toUpperCase();
        });
    </script>

</x-interfaz.layout.app>
