@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => '',
    'placeholder' => null,
])

<div class="mb-4">
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <select
        {{ $attributes->merge([
            'id' => $name,
            'name' => $name,
            'class' => 'block w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-black-900 shadow-sm transition duration-200 focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:bg-gray-100 '
                . ($errors->has($name)
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                    : 'border-gray-300 focus:border-blue-600 focus:ring-blue-300'),
        ]) }}
    >
        <option value="">{{ $placeholder }}</option>

        @foreach ($options as $key => $text)
            <option value="{{ $key }}" @selected((string) old($name, $value) === (string) $key)>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @error($name)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>