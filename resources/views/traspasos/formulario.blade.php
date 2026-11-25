@php
    $filasIniciales = old('productos') ?: [['articulo_id' => $inicial['articulo_id'], 'cantidad' => $inicial['cantidad']]];
    $origenActual = (int) old('almacen_id', $origen);
@endphp

<x-interfaz.layout.app titulo="Pedir material a otro almacén" :rol="$rol" :usuario="$nombre">

    <a href="{{ route('traspaso.index') }}" class="mb-4 inline-block text-sm font-semibold text-marca-900 hover:underline">← Traspasos entre almacenes</a>

    <x-interfaz.panel.cabecera titulo="Pedir material a otro almacén"
        subtitulo="El otro almacén verá la petición en «Traspasos · Me piden» y te lo enviará" />

    <x-interfaz.panel.avisos />

    @if ($almacenes->count() < 2)
        <p class="rounded-lg border border-dashed border-slate-300 py-10 text-center text-slate-500">
            Hace falta tener al menos dos almacenes para pedir material entre ellos.
        </p>
    @else
        <form method="POST" action="{{ route('traspaso.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-interfaz.panel.tarjeta titulo="Origen y destino">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="almacen_id" class="mb-1 block text-sm font-medium text-slate-700">Pedir a (almacén que tiene el material)</label>
                        <select id="almacen_id" name="almacen_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                            @foreach ($almacenes as $a)
                                <option value="{{ $a->id }}" @selected($origenActual === $a->id)>{{ $a->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="almacen_destino_id" class="mb-1 block text-sm font-medium text-slate-700">Para (almacén que lo recibe)</label>
                        <select id="almacen_destino_id" name="almacen_destino_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                            @foreach ($almacenes as $a)
                                <option value="{{ $a->id }}" @selected((int) old('almacen_destino_id', $destino) === $a->id)>{{ $a->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-formulario.entrada name="fecha_necesaria" type="date" label="Lo necesitas el" />
                    <x-formulario.seleccion name="curso_id" label="Para el curso (opcional)" placeholder="Reposición de stock"
                        :value="$inicial['curso_id']"
                        :options="$cursos->mapWithKeys(fn ($c) => [$c->id => ($c->formacion?->nombre ?? 'Curso ' . $c->codigo_accion) . ' · ' . $c->fecha_inicio->format('d/m')])->all()" />
                    <x-formulario.area name="observaciones" label="Nota para el otro almacén (opcional)" contenedor="md:col-span-2" rows="2" />
                </div>
            </x-interfaz.panel.tarjeta>

            <x-interfaz.panel.tarjeta titulo="Material">
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-4 py-3">Artículo</th>
                                <th class="px-4 py-3">En el almacén de origen</th>
                                <th class="w-40 px-4 py-3">Cantidad</th>
                                <th class="w-24 px-4 py-3"><span class="sr-only">Quitar</span></th>
                            </tr>
                        </thead>
                        <tbody id="filas" class="divide-y divide-slate-200">
                            @foreach ($filasIniciales as $i => $fila)
                                <tr class="fila">
                                    <td class="px-4 py-3">
                                        <select name="productos[{{ $i }}][articulo_id]" aria-label="Artículo" class="sel-articulo w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                                            <option value="">-- Elige un artículo --</option>
                                            @foreach ($articulos as $a)
                                                <option value="{{ $a->id }}" @selected((string) ($fila['articulo_id'] ?? '') === (string) $a->id)>{{ $a->nombre }} ({{ $a->codigo }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3"><span class="stock-origen font-semibold text-slate-600">—</span></td>
                                    <td class="px-4 py-3">
                                        <input type="number" min="1" name="productos[{{ $i }}][cantidad]" value="{{ $fila['cantidad'] ?? '' }}" aria-label="Cantidad" class="inp-cantidad w-full rounded-lg border border-slate-300 px-3 py-2">
                                    </td>
                                    <td class="px-4 py-3 text-right"><button type="button" class="btn-quitar text-sm font-semibold text-red-700 hover:underline">Quitar</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" id="anadir" class="mt-3 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-900 hover:bg-stone-50">+ Añadir artículo</button>
            </x-interfaz.panel.tarjeta>

            <div class="flex justify-end gap-3">
                <a href="{{ route('traspaso.index') }}" class="rounded-lg border border-stone-300 bg-white px-5 py-2.5 text-sm font-semibold text-marca-900 hover:bg-stone-50">Cancelar</a>
                <button type="submit" class="rounded-lg bg-marca-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-marca-900">Enviar petición</button>
            </div>
        </form>

        <script>
            // Stock por artículo y almacén: { articulo_id: { almacen_id: cantidad } }
            const stock = @json($stock);
            const filas = document.getElementById('filas');
            const origen = document.getElementById('almacen_id');

            function actualizar() {
                filas.querySelectorAll('.fila').forEach(fila => {
                    const art = fila.querySelector('.sel-articulo').value;
                    const cant = parseInt(fila.querySelector('.inp-cantidad').value || '0', 10);
                    const hay = art ? ((stock[art] || {})[origen.value] || 0) : null;
                    const span = fila.querySelector('.stock-origen');
                    span.textContent = hay === null ? '—' : hay + (cant > hay ? ' · no tiene tantos' : ' disponibles');
                    span.className = 'stock-origen font-semibold ' + (hay === null ? 'text-slate-600' : (cant > hay ? 'text-red-700' : 'text-emerald-700'));
                });
            }

            function renumerar() {
                filas.querySelectorAll('.fila').forEach((fila, i) => {
                    fila.querySelector('.sel-articulo').name = `productos[${i}][articulo_id]`;
                    fila.querySelector('.inp-cantidad').name = `productos[${i}][cantidad]`;
                });
            }

            document.getElementById('anadir').addEventListener('click', () => {
                const nueva = filas.querySelector('.fila').cloneNode(true);
                nueva.querySelector('.sel-articulo').value = '';
                nueva.querySelector('.inp-cantidad').value = '';
                filas.appendChild(nueva);
                renumerar();
                actualizar();
            });

            filas.addEventListener('click', e => {
                if (e.target.classList.contains('btn-quitar') && filas.querySelectorAll('.fila').length > 1) {
                    e.target.closest('.fila').remove();
                    renumerar();
                    actualizar();
                }
            });
            filas.addEventListener('input', actualizar);
            filas.addEventListener('change', actualizar);
            origen.addEventListener('change', actualizar);
            actualizar();
        </script>
    @endif

</x-interfaz.layout.app>
