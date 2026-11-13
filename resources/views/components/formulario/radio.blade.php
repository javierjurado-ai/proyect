@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'contenedor' => '',
])

@php $actual = (string) old($name, $value); @endphp

<div class="{{ $contenedor }}">
    @if ($label)
        <span class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</span>
    @endif

    <div class="flex gap-6 py-2">
        @foreach ($options as $valor => $texto)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="radio" name="{{ $name }}" value="{{ $valor }}"
                       @checked($actual === (string) $valor)
                       {{ $attributes->merge(['class' => 'h-4 w-4 accent-blue-600']) }}>
                {{ $texto }}
            </label>
        @endforeach
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>