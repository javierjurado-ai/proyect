<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Centro</title>
</head>
<body>
       <form method="POST" action="{{ route('centro.agregarCentro') }}">
        @csrf

        <x-formulario.tarjeta>
            @if (session('status'))
                <p class="mb-4 text-sm text-green-600">{{ session('status') }}</p>
            @endif

            @error('api')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <x-formulario.seccion titulo="Datos Curso">
                <x-formulario.entrada name="nombre" label="Nombre*" type="text" />
                <x-formulario.entrada name="responsable" label="Responsable" type="text" />
                <x-formulario.entrada name="direccion" label="Direccion*" type="text" />
                <x-formulario.entrada name="telefono" label="Telefono*" type="number" />
                <x-formulario.entrada name="email" label="Email" type="text" />
                <x-formulario.selector name="empresa_id" label="Empresa*" placeholder="Busca una empresa..."
                    :options="$empresas" />
            </x-formulario.seccion>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="submit" class="w-full">Guardar</x-formulario.boton>
            </div>
        </x-formulario.tarjeta>
    </form>
</body>
</html>