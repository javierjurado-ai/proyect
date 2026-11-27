@props(['action'])

{{-- Caja para escribir y enviar mensajes. Enter envía, Mayús+Enter salta de línea --}}
<form method="POST" action="{{ $action }}" class="border-t border-slate-200 bg-white p-3" data-chat-formulario>
    @csrf

    <div class="flex items-end gap-2">
        <textarea name="contenido" rows="1" required maxlength="2000" placeholder="Escribe un mensaje"
            class="max-h-32 min-h-[40px] flex-1 resize-none rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-teal-800 focus:ring-1 focus:ring-teal-800 focus:outline-none"
            data-chat-input>{{ old('contenido') }}</textarea>

        <button type="submit"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-800 text-white transition hover:bg-teal-900"
            aria-label="Enviar">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
            </svg>
        </button>
    </div>

    @error('contenido')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</form>
