<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($formacion) ? 'Editar Formación' : 'Crear Formación' }}</title>
    <title>Formación</title>
    <script>
        const expira = Number(sessionStorage.getItem('expira')); 

        if (!token || Date.now() / 1000 > expira) {
            sessionStorage.clear();
            window.location.href = '/persona/iniciarSesion';
        }
    </script>
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

        <form method="POST" action="{{ isset($formacion) ? route('formacion.update', $formacion->id) : route('formacion.store') }}" id="formacion-form">
            @csrf
            @if(isset($formacion))
                @method('PUT')
            @endif

            <h2 class="text-2xl font-bold text-slate-800 mb-1">Formación</h2>
            <p class="text-sm text-slate-500 mb-6">Los campos marcados con * son obligatorios</p>

            <div class="space-y-4 mb-6">
                <x-formulario.entrada name="nombre" label="Nombre *" maxlength="100" :value="$formacion?->nombre ?? old('nombre')" required />
                <x-formulario.entrada name="codigo_accion" label="Código Acción *" type="number" :value="$formacion?->codigo_accion ?? old('codigo_accion')" required />
                <x-formulario.radio name="modalidad" label="Modalidad *" :options="$modalidades" :value="$formacion?->modalidad?->value ?? old('modalidad')" />
                <x-formulario.entrada name="sector" label="Sector *" :value="$formacion?->sector ?? old('sector')" required />
                <x-formulario.entrada name="horas_totales" label="Horas Totales *" type="number" min="0" :value="$formacion?->horas_totales ?? old('horas_totales')" />
                <x-formulario.checkbox name="activo" label="Formación activa" :checked="old('activo', isset($formacion) ? $formacion->activo : false)" />
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="reset" class="px-5 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition">Limpiar</button>
                <x-formulario.boton type="submit">{{ isset($formacion) ? 'Actualizar formación' : 'Guardar formación' }}</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>

</body>
</html>