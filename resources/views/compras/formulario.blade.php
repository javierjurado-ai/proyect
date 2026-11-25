@php
    $filas = old('lineas') ?: [['articulo_id' => '', 'concepto' => '', 'cantidad' => 1, 'precio_unitario' => '', 'tipo_iva' => 21, 'numeros_serie' => '']];
    $conSerie = $articulos->filter(fn ($a) => $a->controla_serie?->value === 'SI')->pluck('id')->values();
    $datosArticulos = $articulos->mapWithKeys(fn ($a) => [$a->id => ['precio' => (float) $a->precio_coste, 'iva' => (float) ($a->tipo_iva ?? 21)]]);
@endphp

<x-interfaz.layout.app titulo="Registrar compra" :rol="$rol" :usuario="$nombre">

    <a href="{{ route('compra.index') }}" class="mb-4 inline-block text-sm font-semibold text-marca-900 hover:underline">← Compras</a>

    <x-interfaz.panel.cabecera titulo="Registrar compra"
        subtitulo="Copia los datos de la factura del proveedor. Si marcas la entrada, el material entra en el almacén al guardar." />

    <x-interfaz.panel.avisos />

    <form method="POST" action="{{ route('compra.store') }}" class="flex flex-col gap-6">
        @csrf

        <x-interfaz.panel.tarjeta titulo="Datos de la factura">
            <div class="grid gap-4 md:grid-cols-3">
                <x-formulario.seleccion name="empresa_id" label="Proveedor" placeholder="-- Elige el proveedor --" contenedor="md:col-span-2"
                    :options="$proveedores->mapWithKeys(fn ($e) => [$e->id => $e->nombre . ($e->cif ? ' · ' . $e->cif : '')])->all()" />
                <x-formulario.entrada name="numero_factura" label="Nº de factura del proveedor" required />
                <x-formulario.entrada name="fecha" type="date" label="Fecha de la factura" :value="today()->toDateString()" required />
                <x-formulario.entrada name="fecha_vencimiento" type="date" label="Vence el" />
                <x-formulario.seleccion name="forma_pago" label="Forma de pago" value="TRANSFERENCIA"
                    :options="['TRANSFERENCIA' => 'Transferencia', 'DOMICILIACION' => 'Domiciliación', 'TARJETA' => 'Tarjeta', 'EFECTIVO' => 'Efectivo']" />
                <x-formulario.seleccion name="curso_id" label="Imputar a un curso (opcional)" placeholder="Material general" contenedor="md:col-span-2"
                    :options="$cursos->mapWithKeys(fn ($c) => [$c->id => ($c->formacion?->nombre ?? 'Curso ' . $c->codigo_accion) . ' · ' . $c->fecha_inicio->format('d/m/Y')])->all()" />
                <x-formulario.entrada name="retencion_irpf" type="number" step="0.01" min="0" label="Retención IRPF (€)" placeholder="0,00" />
            </div>
        </x-interfaz.panel.tarjeta>

        <x-interfaz.panel.tarjeta titulo="Líneas">
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-3 py-3">Artículo</th>
                            <th class="px-3 py-3">Concepto</th>
                            <th class="w-24 px-3 py-3">Cantidad</th>
                            <th class="w-28 px-3 py-3">Precio (€)</th>
                            <th class="w-20 px-3 py-3">IVA %</th>
                            <th class="w-28 px-3 py-3 text-right">Importe</th>
                            <th class="w-16 px-3 py-3"><span class="sr-only">Quitar</span></th>
                        </tr>
                    </thead>
                    <tbody id="lineas">
                        @foreach ($filas as $i => $l)
                            <tr class="linea border-t border-slate-200 align-top">
                                <td class="px-3 py-2">
                                    <select data-campo="articulo_id" aria-label="Artículo" class="w-full rounded-lg border border-slate-300 bg-white px-2 py-2">
                                        <option value="">Sin artículo (servicio)</option>
                                        @foreach ($articulos as $a)
                                            <option value="{{ $a->id }}" @selected((string) ($l['articulo_id'] ?? '') === (string) $a->id)>{{ $a->nombre }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input data-campo="concepto" value="{{ $l['concepto'] ?? '' }}" placeholder="Se usa el nombre del artículo" aria-label="Concepto" class="w-full rounded-lg border border-slate-300 px-2 py-2">
                                    <div class="series mt-2 hidden">
                                        <input data-campo="numeros_serie" value="{{ is_array($l['numeros_serie'] ?? null) ? implode(', ', $l['numeros_serie']) : ($l['numeros_serie'] ?? '') }}"
                                            placeholder="Nº de serie, separados por comas" aria-label="Números de serie" class="w-full rounded-lg border border-amber-300 bg-amber-50 px-2 py-2 text-xs">
                                    </div>
                                </td>
                                <td class="px-3 py-2"><input data-campo="cantidad" type="number" min="0.01" step="any" value="{{ $l['cantidad'] ?? 1 }}" aria-label="Cantidad" class="w-full rounded-lg border border-slate-300 px-2 py-2"></td>
                                <td class="px-3 py-2"><input data-campo="precio_unitario" type="number" min="0" step="0.01" value="{{ $l['precio_unitario'] ?? '' }}" aria-label="Precio unitario" class="w-full rounded-lg border border-slate-300 px-2 py-2"></td>
                                <td class="px-3 py-2"><input data-campo="tipo_iva" type="number" min="0" max="100" step="any" value="{{ $l['tipo_iva'] ?? 21 }}" aria-label="IVA" class="w-full rounded-lg border border-slate-300 px-2 py-2"></td>
                                <td class="importe px-3 py-2 text-right font-semibold">0,00 €</td>
                                <td class="px-3 py-2 text-right"><button type="button" class="quitar text-sm font-semibold text-red-700 hover:underline">Quitar</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" id="anadir" class="mt-3 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">+ Añadir línea</button>

            <dl class="ml-auto mt-4 grid w-full max-w-xs grid-cols-2 gap-y-1 text-sm">
                <dt class="text-slate-600">Base imponible</dt><dd id="t-base" class="text-right">0,00 €</dd>
                <dt class="text-slate-600">IVA</dt><dd id="t-iva" class="text-right">0,00 €</dd>
                <dt class="text-slate-600">Retención</dt><dd id="t-irpf" class="text-right">0,00 €</dd>
                <dt class="border-t border-slate-200 pt-1 font-bold">Total</dt><dd id="t-total" class="border-t border-slate-200 pt-1 text-right font-bold">0,00 €</dd>
            </dl>
        </x-interfaz.panel.tarjeta>

        <x-interfaz.panel.tarjeta titulo="Entrada en almacén">
            <label class="flex items-start gap-3 text-sm">
                <input type="hidden" name="generar_entrada" value="0">
                <input type="checkbox" id="generar_entrada" name="generar_entrada" value="1" @checked(old('generar_entrada', '1') == '1') class="mt-0.5 h-4 w-4">
                <span><strong>Dar entrada al material ahora.</strong> Las líneas con artículo suman stock en el almacén elegido; los equipos con nº de serie se crean como disponibles.</span>
            </label>
            <div id="bloque-almacen" class="mt-4 max-w-sm">
                <x-formulario.seleccion name="almacen_id" label="Almacén donde entra" :value="$almacenId"
                    :options="$almacenes->pluck('nombre', 'id')->all()" />
            </div>
            <x-formulario.area name="observaciones" label="Observaciones (opcional)" contenedor="mt-4" rows="2" />
        </x-interfaz.panel.tarjeta>

        <div class="flex justify-end gap-3">
            <a href="{{ route('compra.index') }}" class="rounded-lg border border-stone-300 bg-white px-5 py-2.5 text-sm font-semibold text-marca-900 hover:bg-stone-50">Cancelar</a>
            <button type="submit" class="rounded-lg bg-marca-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-marca-900">Guardar compra</button>
        </div>
    </form>

    <script>
        const conSerie = @json($conSerie);
        const articulos = @json($datosArticulos);
        const cuerpo = document.getElementById('lineas');
        const euros = n => n.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
        const num = v => parseFloat(String(v).replace(',', '.')) || 0;

        function renumerar() {
            cuerpo.querySelectorAll('.linea').forEach((fila, i) => {
                fila.querySelectorAll('[data-campo]').forEach(el => el.name = `lineas[${i}][${el.dataset.campo}]`);
            });
        }

        function recalcular() {
            let base = 0, iva = 0;
            cuerpo.querySelectorAll('.linea').forEach(fila => {
                const v = c => fila.querySelector(`[data-campo="${c}"]`).value;
                const importe = num(v('cantidad')) * num(v('precio_unitario'));
                base += importe;
                iva += importe * num(v('tipo_iva')) / 100;
                fila.querySelector('.importe').textContent = euros(importe);
                fila.querySelector('.series').classList.toggle('hidden', !conSerie.includes(parseInt(v('articulo_id'), 10)));
            });
            const irpf = num(document.getElementById('retencion_irpf').value);
            document.getElementById('t-base').textContent = euros(base);
            document.getElementById('t-iva').textContent = euros(iva);
            document.getElementById('t-irpf').textContent = irpf ? '−' + euros(irpf) : euros(0);
            document.getElementById('t-total').textContent = euros(base + iva - irpf);
            document.getElementById('bloque-almacen').classList.toggle('hidden', !document.getElementById('generar_entrada').checked);
        }

        // Al elegir artículo: propone precio de coste e IVA si están vacíos
        cuerpo.addEventListener('change', e => {
            if (e.target.dataset.campo === 'articulo_id' && articulos[e.target.value]) {
                const fila = e.target.closest('.linea');
                const precio = fila.querySelector('[data-campo="precio_unitario"]');
                if (!precio.value && articulos[e.target.value].precio) precio.value = articulos[e.target.value].precio;
                fila.querySelector('[data-campo="tipo_iva"]').value = articulos[e.target.value].iva;
            }
            recalcular();
        });
        cuerpo.addEventListener('input', recalcular);
        cuerpo.addEventListener('click', e => {
            if (e.target.classList.contains('quitar') && cuerpo.querySelectorAll('.linea').length > 1) {
                e.target.closest('.linea').remove();
                renumerar();
                recalcular();
            }
        });
        document.getElementById('anadir').addEventListener('click', () => {
            const nueva = cuerpo.querySelector('.linea').cloneNode(true);
            nueva.querySelectorAll('input').forEach(el => el.value = '');
            nueva.querySelector('select').value = '';
            nueva.querySelector('[data-campo="cantidad"]').value = 1;
            nueva.querySelector('[data-campo="tipo_iva"]').value = 21;
            cuerpo.appendChild(nueva);
            renumerar();
            recalcular();
        });
        document.getElementById('retencion_irpf').addEventListener('input', recalcular);
        document.getElementById('generar_entrada').addEventListener('change', recalcular);
        renumerar();
        recalcular();
    </script>

</x-interfaz.layout.app>
