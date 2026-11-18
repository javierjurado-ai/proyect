@props(['href'])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300']) }}>
    + {{ $slot }}
</a>

