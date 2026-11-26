@php
    $editando = isset($credito);
    $euros    = fn ($n) => number_format((float) $n, 2, ',', '.') . ' €';
    $volver   = route('credito.index', ['anio' => $editando ? $credito->año : ($anioPorDefecto ?? null)]);
@endphp

<x-interfaz.layout.app :titulo="$editando ? 'Editar crédito' : 'Registrar crédito'" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera
        :titulo="$editando ? 'Editar crédito formativo' : 'Registrar crédito formativo'"
        subtitulo="Los campos marcados con * son obligatorios">
        <x-slot:acciones>
            <a href="{{ $volver }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver a créditos
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            Revisa los campos marcados en rojo ({{ $errors->count() }} {{ $errors->count() === 1 ? 'error' : 'errores' }}).
        </div>
    @endif

    {{-- Al editar: lo que dicen los costes de los cursos de ese año --}}
    @if ($editando)
        <div class="mb-6 rounded-xl border px-5 py-4 text-sm {{ $fila->enCursos > $fila->total ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-marca-100 bg-marca-50 text-marca-900' }}">
            <p>
                En los cursos de <strong>{{ $credito->año }}</strong> hay <strong>{{ $euros($fila->enCursos) }}</strong>
                imputados a esta empresa ({{ $fila->numCursos }} {{ $fila->numCursos === 1 ? 'curso' : 'cursos' }}, según sus costes).
                @if ($fila->enCursos > $fila->total)
                    Es más que su crédito total, así que una parte no se podrá bonificar.
                @endif
            </p>
            @if ($fila->enCursos > 0 && $fila->enCursos <= $fila->total && abs($fila->enCursos - $fila->consumido) > 0.005)
                <button type="button" id="usar-cursos" data-importe="{{ number_format($fila->enCursos, 2, '.', '') }}"
                        class="mt-3 inline-flex items-center rounded-lg border border-marca-700 bg-white px-3 py-1.5 text-xs font-semibold text-marca-900 hover:bg-marca-50">
                    Usar {{ $euros($fila->enCursos) }} como consumido
                </button>
            @endif
        </div>
    @endif

    <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
        <form id="credito-form" method="POST"
              action="{{ $editando ? route('credito.update', $credito->id) : route('credito.store') }}">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <fieldset class="mb-8">
                <legend class="mb-3 text-sm font-semibold uppercase tracking-wide text-marca-800">Crédito FUNDAE</legend>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
                    <x-formulario.seleccion
                        name="empresa_id"
                        label="Empresa *"
                        placeholder="-- Selecciona una empresa --"
                        contenedor="md:col-span-3"
                        :options="$empresas"
                        :value="$editando ? $credito->empresa_id : ($empresaPorDefecto ?? '')" />

                    <x-formulario.entrada
                        name="anio"
                        label="Año *"
                        type="number"
                        min="2000"
                        max="2100"
                        step="1"
                        :value="$editando ? $credito->año : $anioPorDefecto" />

                    <x-formulario.entrada
                        name="importe_total"
                        label="Crédito total (€) *"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0,00"
                        contenedor="md:col-span-2"
                        :value="$credito->importe_total ?? ''" />

                    <x-formulario.entrada
                        name="importe_consumido"
                        label="Importe consumido (€)"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0,00"
                        contenedor="md:col-span-2"
                        :value="$credito->importe_consumido ?? ''" />
                </div>
                <p class="mt-3 text-xs text-slate-500">
                    El crédito total es el que FUNDAE asigna a la empresa para ese año (se consulta en su área privada).
                    Si dejas el consumido vacío se guarda como 0.
                </p>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ $volver }}"
                   class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-stone-100">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                    {{ $editando ? 'Actualizar crédito' : 'Guardar crédito' }}
                </button>
            </div>
        </form>
    </section>

    <script>
        // Copia lo imputado en cursos al campo "consumido"
        document.getElementById('usar-cursos')?.addEventListener('click', (e) => {
            document.getElementById('importe_consumido').value = e.currentTarget.dataset.importe;
        });
    </script>

</x-interfaz.layout.app>
