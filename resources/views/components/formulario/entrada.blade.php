@props([
    'name',
    'label' => null,
    'value' => '',
])

<div class="mb-4">
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <input
        {{ $attributes->merge([
            'type' => 'text',
            'id' => $name,
            'name' => $name,
            'value' => old($name, $value),
            'class' => 'block w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-black-900 shadow-sm placeholder-gray-400 transition duration-200 focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:bg-gray-100 '
                . ($errors->has($name)
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                    : 'border-gray-300 focus:border-blue-600 focus:ring-blue-300'),
        ]) }}
    >

    @error($name)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>