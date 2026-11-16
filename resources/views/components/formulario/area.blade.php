@props([
    'name',
    'label' => null,
    'value' => '',
    'contenedor' => '',
])

<div class="{{ $contenedor }}">
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    <textarea
        {{ $attributes->merge([
            'id' => $name,
            'name' => $name,
            'rows' => 3,
            'class' => 'w-full rounded-lg border px-3 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 '
                . ($errors->has($name)
                    ? 'border-red-500 focus:ring-red-300'
                    : 'border-slate-300 focus:ring-blue-500'),
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($attributes->has('maxlength'))
        <p class="mt-1 text-right text-xs text-slate-400">
            <span data-contador="{{ $name }}">{{ mb_strlen(old($name, $value)) }}</span>/{{ $attributes->get('maxlength') }}
        </p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>