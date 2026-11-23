<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto de Centro</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-100 min-h-screen py-10 px-4">

    <x-formulario.tarjeta class="max-w-xl bg-white">

        @if(session('success'))
            <div id="success-alert" class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('centro_contactos.store') }}" method="POST" id="contacto-centro-form">
            @csrf

            <h2 class="text-2xl font-bold text-slate-800 mb-1">Contacto de Centro</h2>
            <p class="text-sm text-slate-500 mb-6">Los campos marcados con * son obligatorios</p>

            <div class="space-y-4 mb-6">
                {{-- Desplegable Centro --}}
                <div class="mb-4">
                    <label for="centro_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Centro *
                    </label>
                    <select
                        id="centro_id"
                        name="centro_id"
                        class="block w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition duration-200 focus:outline-none focus:ring-4 {{ $errors->has('centro_id') ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-gray-300 focus:border-blue-600 focus:ring-blue-300' }}"
                    >
                        <option value="">-- Selecciona un centro --</option>
                        @foreach($centros as $id => $nombreCentro)
                            <option value="{{ $id }}" {{ old('centro_id') == $id ? 'selected' : '' }}>
                                {{ $nombreCentro }}
                            </option>
                        @endforeach
                    </select>
                    @error('centro_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nombre del contacto --}}
                <x-formulario.entrada name="nombre" label="Nombre *" maxlength="45" />

                {{-- Desplegable Tipo de Contacto (Enum) --}}
                <div class="mb-4">
                    <label for="contacto" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Tipo de contacto *
                    </label>
                    <select
                        id="contacto"
                        name="contacto"
                        class="block w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition duration-200 focus:outline-none focus:ring-4 {{ $errors->has('contacto') ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-gray-300 focus:border-blue-600 focus:ring-blue-300' }}"
                    >
                        <option value="">-- Selecciona un tipo --</option>
                        @foreach($tiposContacto as $valor => $etiqueta)
                            <option value="{{ $valor }}" {{ old('contacto') == $valor ? 'selected' : '' }}>
                                {{ $etiqueta }}
                            </option>
                        @endforeach
                    </select>
                    @error('contacto')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Teléfono y Email personal --}}
                <x-formulario.entrada name="telefono_personal" label="Teléfono personal" type="tel" maxlength="45" />
                <x-formulario.entrada name="email_personal" label="Email personal" type="email" maxlength="45" />
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="reset" class="px-5 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition">Limpiar</button>
                <x-formulario.boton type="submit">Guardar contacto</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>

    @if(session('success'))
    <script>
        document.getElementById('contacto-centro-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>
    @endif

</body>
</html>
