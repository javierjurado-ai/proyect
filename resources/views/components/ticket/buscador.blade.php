@props(['titulo' => 'Chats'])

<div class="border-b border-slate-200 p-4">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800">{{ $titulo }}</h2>
        {{ $slot }}
    </div>

    <form method="GET" action="{{ url()->current() }}">
        <div class="relative">
            <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z" />
            </svg>
            <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar conversación"
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pr-3 pl-9 text-sm text-slate-700 placeholder:text-slate-400 focus:border-teal-800 focus:ring-1 focus:ring-teal-800 focus:outline-none">
        </div>
    </form>
</div>
