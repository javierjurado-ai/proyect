@props(['type' => 'button']) {{--valor por defecto button--}}

<button
    type="{{ $type }}" 
    {{ $attributes->merge([
        'class' => 'flex w-fit ml-auto items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-mono text-white shadow-md shadow-blue-600/30 transition duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-300 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50'
    ]) }}
>
    {{ $slot }}
</button>