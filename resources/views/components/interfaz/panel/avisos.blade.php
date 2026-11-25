{{-- Mensaje de éxito y errores tras enviar un formulario --}}
@if (session('exito'))
    <div role="status" class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('exito') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
        @if ($errors->count() === 1)
            {{ $errors->first() }}
        @else
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
