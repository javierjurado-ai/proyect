@props(['href' => '#'])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-amber-600 focus:outline-none focus:ring-4 focus:ring-amber-300']) }}>
    {{ $slot->isEmpty() ? 'Editar' : $slot }}
</a>