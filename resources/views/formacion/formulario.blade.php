<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formación</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-100 min-h-screen py-10 px-4">

    <x-formulario.tarjeta class="max-w-xl bg-white">

        @if (session('status'))
            <div id="success-alert" class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        @error('api')
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700 shadow-sm">
                {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('formacion.store') }}" id="formacion-form">
            @csrf

            <h2 class="text-2xl font-bold text-slate-800 mb-1">Formación</h2>
            <p class="text-sm text-slate-500 mb-6">Los campos marcados con * son obligatorios</p>

            <div class="space-y-4 mb-6">
                <x-formulario.entrada name="nombre" label="Nombre *" maxlength="100" />
                <x-formulario.entrada name="codigo_accion" label="Código Acción *" type="number" />
                <x-formulario.radio name="modalidad" label="Modalidad *" :options="$modalidades" />
                <x-formulario.entrada name="sector" label="Sector *" />
                <x-formulario.entrada name="horas_totales" label="Horas Totales *" type="number" min="0" />
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="reset" class="px-5 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition">Limpiar</button>
                <x-formulario.boton type="submit">Guardar formación</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>

    @if (session('status'))
    <script>
        document.getElementById('formacion-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>
    @endif

</body>
</html>