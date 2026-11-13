@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => '',
    'placeholder' => '-- Selecciona --',
    'contenedor' => '',
])

@php $actual = (string) old($name, $value); @endphp

<div class="{{ $contenedor }}">
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    <select
        {{ $attributes->merge([
            'id' => $name,
            'name' => $name,
            'class' => 'w-full rounded-lg border bg-white px-3 py-2 text-slate-900 focus:outline-none focus:ring-2 '
                . ($errors->has($name)
                    ? 'border-red-500 focus:ring-red-300'
                    : 'border-slate-300 focus:ring-blue-500'),
        ]) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $valor => $texto)
            <option value="{{ $valor }}" @selected($actual === (string) $valor)>{{ $texto }}</option>
        @endforeach
    </select>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>