
@props([
    'tipo' => 'texto',
    'valor' => null,
    'color' => 'slate',
    'vacio' => '—',
])

@php
    // Los enums se muestran por su valor
    if ($valor instanceof \BackedEnum) {
        $valor = $valor->value;
    }

    $sinValor = $valor === null || $valor === '';

    $clases = [
        'texto'     => 'text-slate-700',
        'destacado' => 'font-medium text-slate-800',
        'mono'      => 'font-mono text-slate-700',
        'numero'    => 'text-right text-slate-700',
        'moneda'    => 'whitespace-nowrap text-right text-slate-700',
        'fecha'     => 'whitespace-nowrap text-slate-700',
        'hora'      => 'whitespace-nowrap text-slate-700',
        'etiqueta'  => 'text-slate-700',
        'sino'      => 'text-center',
    ][$tipo] ?? 'text-slate-700';

    $colores = [
        'slate'  => 'bg-slate-100 text-slate-700',
        'blue'   => 'bg-blue-100 text-blue-800',
        'green'  => 'bg-green-100 text-green-800',
        'red'    => 'bg-red-100 text-red-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'amber'  => 'bg-amber-100 text-amber-800',
    ];

    $texto = match (true) {
        $sinValor                => $vacio,
        $tipo === 'moneda'       => number_format((float) $valor, 2, ',', '.') . ' €',
        $tipo === 'fecha'        => \Illuminate\Support\Carbon::parse($valor)->format('d/m/Y'),
        $tipo === 'hora'         => substr((string) $valor, 0, 5),
        $tipo === 'etiqueta'     => \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower((string) $valor)),
        default                  => $valor,
    };
@endphp

<td {{ $attributes->merge(['class' => "px-4 py-3 $clases"]) }}>
    @if ($slot->hasActualContent())
        {{ $slot }}
    @elseif ($tipo === 'sino')
        @if ($valor && ! in_array(\Illuminate\Support\Str::upper((string) $valor), ['NO', '0', 'FALSE'], true))
            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Sí</span>
        @else
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">No</span>
        @endif
    @elseif ($tipo === 'etiqueta' && ! $sinValor)
        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $colores[$color] ?? $colores['slate'] }}">{{ $texto }}</span>
    @else
        {{ $texto }}
    @endif
</td>