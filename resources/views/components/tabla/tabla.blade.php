{{-- columnas: 'Texto' o ['Texto', 'right'|'center'] --}}
@props([
    'columnas' => [],
    'vacio' => 'Todavía no hay registros.',
    'paginacion' => null,
])

@if (! $slot->hasActualContent())
    <p class="rounded-lg border border-dashed border-slate-300 py-10 text-center text-slate-500">
        {{ $vacio }}
    </p>
@else
    <div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-lg border border-slate-200']) }}>
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                <tr>
                    @foreach ($columnas as $columna)
                        @php
                            [$texto, $alinear] = is_array($columna) ? $columna : [$columna, 'left'];
                            $claseAlinear = ['left' => 'text-left', 'right' => 'text-right', 'center' => 'text-center'][$alinear] ?? 'text-left';
                        @endphp
                        <th class="whitespace-nowrap px-4 py-3 {{ $claseAlinear }}">{{ $texto }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>
@endif

@if ($paginacion && method_exists($paginacion, 'links') && $paginacion->hasPages())
    <div class="mt-4">{{ $paginacion->links() }}</div>
@endif