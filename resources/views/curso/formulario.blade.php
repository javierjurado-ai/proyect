<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Formulario Curso</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <form method="POST" action="{{ route('curso.store') }}">
        @csrf

        <x-formulario.tarjeta>
            @if (session('status'))
                <p class="mb-4 text-sm text-green-600">{{ session('status') }}</p>
            @endif

            @error('api')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <x-formulario.seccion titulo="Datos Curso">
                <x-formulario.entrada name="codigoCurso" label="Código" type="number" />
                <x-formulario.entrada name="empresaFormativa" label="Empresa" type="text" />
                <x-formulario.entrada name="fechaInicio" label="Fecha Inicio" type="date" />
                <x-formulario.entrada name="fechaFin" label="Fecha Fin" type="date" />
                <x-formulario.entrada name="horaInicio" label="Hora Inicio" type="time" />
                <x-formulario.entrada name="horaFin" label="Hora Fin" type="time" />
                <x-formulario.entrada name="turno" label="Turno" type="text" />
                <x-formulario.entrada name="aula" label="Aula" type="text" class="text-red-700" />
                <x-formulario.entrada name="aulaVirtual" label="Aula Virtual" type="text" />
                <x-formulario.entrada name="tecnicoComunidad" label="Técnico Comunidad" type="text" />
                <x-formulario.entrada name="observaciones" label="Observaciones" type="text" />
                <x-formulario.entrada name="formacion" label="Formación" type="text" />
                <x-formulario.selector name="centro" label="Centro" placeholder="Busca un centro..."
                    :options="$centros" />
                
            </x-formulario.seccion>
            <div class="flex justify-end gap-3">
                    <x-formulario.boton type="submit" class="w-full">Guardar</x-formulario.boton>
                </div>
        </x-formulario.tarjeta>

    </form>

</body>

</html>
