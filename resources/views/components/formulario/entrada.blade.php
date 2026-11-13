@props([
    'name',
    'label' => null,
    'value' => '',
    'contenedor' => '',
])

<div class="{{ $contenedor }}">
    @if ($label)
        <label for="{{ $attributes->get('id', $name) }}" class="mb-1 block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <input
        {{ $attributes->merge([
            'type' => 'text',
            'id' => $name,
            'name' => $name,
            'value' => old($name, $value),
            'class' => 'w-full rounded-lg border px-3 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 '
                . ($errors->has($name)
                    ? 'border-red-500 focus:ring-red-300'
                    : 'border-slate-300 focus:ring-blue-500'),
        ]) }}
    >

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>