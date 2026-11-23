@props(['name', 'label', 'value' => 1, 'checked' => false])

<div class="flex items-center gap-2">
    <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}" 
        {{ $checked ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4']) }}>
    <label for="{{ $name }}" class="text-sm font-medium text-slate-700">{{ $label }}</label>
</div>