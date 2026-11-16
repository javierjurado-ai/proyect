@props([
    'columnas' => [],                       {{-- 'Texto' o ['Texto', 'right'|'center'] --}}
    'vacio' => 'Todavía no hay registros.',
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
                        @endphp
                        <th class="px-4 py-3 whitespace-nowrap text-{{ $alinear }}">{{ $texto }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>
@endif