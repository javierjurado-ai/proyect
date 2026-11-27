@props([
    'propio' => false,
    'hora' => null,
    'leido' => false,
    'autor' => null,
])

{{-- Burbuja de mensaje: a la derecha si es mío, a la izquierda si es del otro --}}
<div @class(['mb-1.5 flex', 'justify-end' => $propio, 'justify-start' => ! $propio])>
    <div @class([
        'max-w-[80%] rounded-lg px-3 py-2 shadow-sm sm:max-w-[65%]',
        'rounded-br-sm bg-teal-800 text-white' => $propio,
        'rounded-bl-sm bg-white text-slate-700' => ! $propio,
    ])>
        @if ($autor && ! $propio)
            <p class="mb-0.5 text-xs font-semibold text-teal-800">{{ $autor }}</p>
        @endif

        <p class="text-sm break-words whitespace-pre-line">{{ $slot }}</p>

        <div @class([
            'mt-1 flex items-center justify-end gap-1 text-[11px]',
            'text-teal-100' => $propio,
            'text-slate-400' => ! $propio,
        ])>
            <span>{{ $hora }}</span>

            @if ($propio)
                {{-- Doble check: más claro cuando el mensaje se ha leído --}}
                <svg @class(['h-3.5 w-3.5', 'text-sky-300' => $leido, 'text-teal-200' => ! $leido])
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12.5 6.5 17 15 8" />
                    <path d="M10.5 16 11.5 17 20 8" />
                </svg>
            @endif
        </div>
    </div>
</div>
